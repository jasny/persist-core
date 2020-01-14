<?php

declare(strict_types=1);

namespace Jasny\DB\Tests\FieldMap;

use Improved as i;
use Jasny\DB\FieldMap\ConfiguredFieldMap;
use Jasny\DB\Filter\FilterItem;
use Jasny\DB\Update\UpdateInstruction;
use PHPUnit\Framework\TestCase;

/**
 * @covers \Jasny\DB\FieldMap\ConfiguredFieldMap
 */
class ConfiguredFieldMapTest extends TestCase
{
    protected const MAP = ['_id' => 'id', 'foos' => 'foo', 'bor' => 'bar'];


    public function testInvalidMapException()
    {
        $this->expectException(\UnexpectedValueException::class);
        $this->expectExceptionMessage("Duplicate field in map: foo, bar");

        new ConfiguredFieldMap([
            '_id' => 'id',
            'abc' => 'foo',
            'def' => 'bar',
            '123' => 'foo',
            '789' => 'bar',
        ]);
    }


    public function testGetMap()
    {
        $fieldMap = new ConfiguredFieldMap(self::MAP);
        $this->assertEquals(self::MAP, $fieldMap->getMap());
    }

    public function testGetInverseMap()
    {
        $fieldMap = new ConfiguredFieldMap(self::MAP);
        $this->assertEquals(['id' => '_id', 'foo' => 'foos', 'bar' => 'bor'], $fieldMap->getInverseMap());
    }


    public function testApplyToFilter()
    {
        $filter = [
            new FilterItem('id', 'not', 42),
            new FilterItem('id', 'min', 1),
            new FilterItem('bar', '', 'hello'),
            new FilterItem('color', 'in', ['blue', 'green']),
        ];

        $fieldMap = new ConfiguredFieldMap(self::MAP);
        $mapped = $fieldMap->applyToFilter($filter);

        $this->assertCount(4, $mapped);

        $this->assertEquals(new FilterItem('_id', 'not', 42), $mapped[0]);
        $this->assertEquals(new FilterItem('_id', 'min', 1), $mapped[1]);
        $this->assertEquals(new FilterItem('bor', '', 'hello'), $mapped[2]);
        $this->assertSame($filter[3], $mapped[3]);
    }

    public function testApplyToFilterWithNestedField()
    {
        $filter = [
            new FilterItem('foo.bar.qux', '', 1),
        ];

        $fieldMap = new ConfiguredFieldMap(self::MAP);
        $mapped = $fieldMap->applyToFilter($filter);

        $this->assertCount(1, $mapped);
        $this->assertEquals(new FilterItem('foos.bar.qux', '', 1), $mapped[0]);
    }


    public function testApplyToUpdate()
    {
        $instructions = [
            new UpdateInstruction('set', ['id' => 42, 'bar' => 'hello']),
            new UpdateInstruction('inc', ['id' => 1, 'foo.bar.qux' => 9]),
            new UpdateInstruction('set', ['color' => 'green']),
        ];

        $fieldMap = new ConfiguredFieldMap(self::MAP);
        $mapped = $fieldMap->applyToUpdate($instructions);

        $this->assertCount(3, $mapped);

        $this->assertEquals(new UpdateInstruction('set', ['_id' => 42, 'bor' => 'hello']), $mapped[0]);
        $this->assertEquals(new UpdateInstruction('inc', ['_id' => 1, 'foos.bar.qux' => 9]), $mapped[1]);
        $this->assertSame($instructions[2], $mapped[2]);
    }


    public function testApplyToResult()
    {
        $items = new \ArrayIterator([
            'array' => ['_id' => 42, 'bor' => 'man', 'color' => 'red'],
            'ArrayObject' => new \ArrayObject(['_id' => 43, 'bor' => 'pop', 'color' => 'green']),
            'object' => (object)['_id' => 50, 'bor' => 'kol', 'color' => 'blue'],
            'string' => 'hello',
        ]);

        $fieldMap = new ConfiguredFieldMap(self::MAP);

        $iterator = $fieldMap->applyToResult($items);
        $mapped = i\iterable_to_array($iterator, true);

        $this->assertArrayHasKey('array', $mapped);
        $this->assertEquals(['id' => 42, 'bar' => 'man', 'color' => 'red'], $mapped['array']);

        $this->assertArrayHasKey('ArrayObject', $mapped);
        $this->assertInstanceOf(\ArrayObject::class, $mapped['ArrayObject']);
        $this->assertEquals(
            ['id' => 43, 'bar' => 'pop', 'color' => 'green'],
            $mapped['ArrayObject']->getArrayCopy()
        );

        $this->assertArrayHasKey('object', $mapped);
        $this->assertEquals((object)['id' => 50, 'bar' => 'kol', 'color' => 'blue'], $mapped['object']);

        $this->assertArrayHasKey('string', $mapped);
        $this->assertEquals('hello', $mapped['string']);

        $this->assertCount(4, $mapped);
    }

    public function testApplyToItems()
    {
        $items = new \ArrayIterator([
            'array' => ['id' => 42, 'bar' => 'man', 'color' => 'red'],
            'ArrayObject' => new \ArrayObject(['id' => 43, 'bar' => 'pop', 'color' => 'green']),
            'object' => (object)['id' => 50, 'bar' => 'kol', 'color' => 'blue'],
            'string' => 'hello',
        ]);

        $fieldMap = new ConfiguredFieldMap(self::MAP);

        $iterator = $fieldMap->applyToItems($items);
        $mapped = i\iterable_to_array($iterator, true);

        $this->assertArrayHasKey('array', $mapped);
        $this->assertEquals(['_id' => 42, 'bor' => 'man', 'color' => 'red'], $mapped['array']);

        $this->assertArrayHasKey('ArrayObject', $mapped);
        $this->assertInstanceOf(\ArrayObject::class, $mapped['ArrayObject']);
        $this->assertEquals(
            ['_id' => 43, 'bor' => 'pop', 'color' => 'green'],
            $mapped['ArrayObject']->getArrayCopy()
        );

        $this->assertArrayHasKey('object', $mapped);
        $this->assertEquals((object)['_id' => 50, 'bor' => 'kol', 'color' => 'blue'], $mapped['object']);

        $this->assertArrayHasKey('string', $mapped);
        $this->assertEquals('hello', $mapped['string']);

        $this->assertCount(4, $mapped);
    }


    public function testSetStateViaVarExport()
    {
        $fieldMap = new ConfiguredFieldMap(self::MAP);

        $code = var_export($fieldMap, true);
        $copy = eval("return {$code};");

        $this->assertEquals($fieldMap, $copy);
    }

    public function testSetStateWithoutInverse()
    {
        $copy = ConfiguredFieldMap::__set_state(['map' => self::MAP]);
        $this->assertEquals(new ConfiguredFieldMap(self::MAP), $copy);
    }

    public function testSetStateWithIncorrectReverse()
    {
        $copy = ConfiguredFieldMap::__set_state(['map' => self::MAP, 'inverse' => ['foo' => 'bar']]);

        $this->assertEquals(self::MAP, $copy->getMap());
        $this->assertEquals(['foo' => 'bar'], $copy->getInverseMap());
    }

    public function testSetStateWithoutMap()
    {
        $this->expectException(\UnexpectedValueException::class);
        $this->expectExceptionMessage("Unable to restore field map; corrupt data");

        ConfiguredFieldMap::__set_state([]);
    }
}
