<?php

declare(strict_types=1);

namespace Jasny\DB\Tests\Map;

use Improved as i;
use Jasny\DB\Map\DeepMap;
use Jasny\DB\Filter\FilterItem;
use Jasny\DB\Update\UpdateInstruction;
use PHPUnit\Framework\TestCase;

/**
 * @covers \Jasny\DB\Map\DeepMap
 */
class DeepMapTest extends TestCase
{
    protected const MAP = [
        'id' => '_id',
        'foo.type' => 'foos.type_id',
        'bar' => 'bor',
        'bar.id' => 'bor.xid',
        'red' => 'color/roja',
        'blue' => 'color/azul',
        'client/organization.id' => 'client_id',
    ];

    public function testForFilter()
    {
        $filter = [
            new FilterItem('foo', '', 0),
            new FilterItem('foo.type', '', 1),
            new FilterItem('foo.type.desc', '', 2),
            new FilterItem('bar', '', ['id' => 11, 'name' => 'eleven']),
            new FilterItem('red', '', 99),
            new FilterItem('blue', '', 77),
            new FilterItem('client.organization', '', ['id' => 'abc']),
        ];

        $map = new DeepMap(self::MAP);
        $applyTo = $map->forFilter();

        $iterator = $applyTo($filter);

        $this->assertIsIterable($iterator);
        $mapped = i\iterable_to_array($iterator, true);

        $this->assertCount(7, $mapped);

        $this->assertSame($filter[0], $mapped[0]);
        $this->assertEquals(new FilterItem('foos.type_id', '', 1), $mapped[1]);
        $this->assertEquals(new FilterItem('foos.type_id.desc', '', 2), $mapped[2]);
        $this->assertEquals(new FilterItem('bor', '', ['xid' => 11, 'name' => 'eleven']), $mapped[3]);
        $this->assertEquals(new FilterItem('color.roja', '', 99), $mapped[4]);
        $this->assertEquals(new FilterItem('color.azul', '', 77), $mapped[5]);
        $this->assertEquals(new FilterItem('client_id', '', 'abc'), $mapped[6]);
    }

    public function testForUpdate()
    {
        $instructions = [
            new UpdateInstruction('set', ['foo' => 99]),
            new UpdateInstruction('set', ['foo.type' => 42]),
            new UpdateInstruction('set', ['foo.type.desc' => 'hello']),
            new UpdateInstruction('set', ['bar' => ['id' => 11, 'name' => 'eleven']]),
            new UpdateInstruction('set', ['red' => 99, 'blue' => 77]),
            new UpdateInstruction('set', ['client.organization' => ['id' => 'abc', 'name' => 'foo']]),
        ];

        $map = new DeepMap(self::MAP);
        $applyTo = $map->forUpdate();

        $iterator = $applyTo($instructions);

        $this->assertIsIterable($iterator);
        $mapped = i\iterable_to_array($iterator, true);

        $this->assertCount(6, $mapped);

        $this->assertSame($instructions[0], $mapped[0]);
        $this->assertEquals(new UpdateInstruction('set', ['foos.type_id' => 42]), $mapped[1]);
        $this->assertEquals(new UpdateInstruction('set', ['foos.type_id.desc' => 'hello']), $mapped[2]);
        $this->assertEquals(new UpdateInstruction('set', ['bor' => ['xid' => 11, 'name' => 'eleven']]), $mapped[3]);
        $this->assertEquals(new UpdateInstruction('set', ['color.roja' => 99, 'color.azul' => 77]), $mapped[4]);
        $this->assertEquals(new UpdateInstruction('set', ['client_id' => 'abc']), $mapped[5]);
    }


    public function testForResult()
    {
        $items = new \ArrayIterator([
            'array' => ['_id' => 42, 'bor' => 'man', 'number' => 3],
            'ArrayObject' => new \ArrayObject(['_id' => 43, 'bor' => 'pop', 'number' => 2]),
            'object' => (object)['_id' => 50, 'bor' => 'kol', 'number' => 1],
            'string' => 'hello',
        ]);

        $map = new DeepMap(self::MAP);
        $applyTo = $map->forResult();

        $iterator = $applyTo($items);

        $this->assertIsIterable($iterator);
        $this->assertIsNotArray($iterator);
        $mapped = i\iterable_to_array($iterator, true);

        $this->assertArrayHasKey('array', $mapped);
        $this->assertEquals(['id' => 42, 'bar' => 'man', 'number' => 3], $mapped['array']);

        $this->assertArrayHasKey('ArrayObject', $mapped);
        $this->assertInstanceOf(\ArrayObject::class, $mapped['ArrayObject']);
        $this->assertEquals(
            ['id' => 43, 'bar' => 'pop', 'number' => 2],
            $mapped['ArrayObject']->getArrayCopy()
        );

        $this->assertArrayHasKey('object', $mapped);
        $this->assertEquals((object)['id' => 50, 'bar' => 'kol', 'number' => 1], $mapped['object']);

        $this->assertArrayHasKey('string', $mapped);
        $this->assertEquals('hello', $mapped['string']);

        $this->assertCount(4, $mapped);
    }

    public function testForItems()
    {
        $items = new \ArrayIterator([
            'array' => ['id' => 42, 'bar' => 'man', 'number' => 3],
            'ArrayObject' => new \ArrayObject(['id' => 43, 'bar' => 'pop', 'number' => 2]),
            'object' => (object)['id' => 50, 'bar' => 'kol', 'number' => 1],
            'string' => 'hello',
        ]);

        $map = new DeepMap(self::MAP);
        $applyTo = $map->forItems();

        $iterator = $applyTo($items);

        $this->assertIsIterable($iterator);
        $this->assertIsNotArray($iterator);
        $mapped = i\iterable_to_array($iterator, true);

        $this->assertArrayHasKey('array', $mapped);
        $this->assertEquals(['_id' => 42, 'bor' => 'man', 'number' => 3], $mapped['array']);

        $this->assertArrayHasKey('ArrayObject', $mapped);
        $this->assertInstanceOf(\ArrayObject::class, $mapped['ArrayObject']);
        $this->assertEquals(
            ['_id' => 43, 'bor' => 'pop', 'number' => 2],
            $mapped['ArrayObject']->getArrayCopy()
        );

        $this->assertArrayHasKey('object', $mapped);
        $this->assertEquals((object)['_id' => 50, 'bor' => 'kol', 'number' => 1], $mapped['object']);

        $this->assertArrayHasKey('string', $mapped);
        $this->assertEquals('hello', $mapped['string']);

        $this->assertCount(4, $mapped);
    }
}
