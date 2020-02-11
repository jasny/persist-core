<?php

declare(strict_types=1);

namespace Jasny\DB\Tests\Map;

use Improved as i;
use Jasny\DB\Map\FieldMap;
use Jasny\DB\Filter\FilterItem;
use Jasny\DB\Update\UpdateInstruction;
use PHPUnit\Framework\TestCase;

/**
 * @covers \Jasny\DB\Map\FieldMap
 */
class FieldMapTest extends TestCase
{
    protected const MAP = [
        'id' => '_id',
        'foo' => 'foos',
        'bar' => 'bor',
        'skippy' => false
    ];

    public function fieldProvider()
    {
        return [
            'id' => ['id', '_id'],
            'foo' => ['foo', 'foos'],
            'bar.xy' => ['bar.xy', 'bor.xy'],
            'foo.bar.qux' => ['foo.bar.qux', 'foos.bar.qux'],
            'numbers' => ['numbers', 'numbers'],
            'skippy' => ['skippy', false],
        ];
    }

    /**
     * @dataProvider fieldProvider
     */
    public function testToDB(string $field, $expected)
    {
        $map = new FieldMap(self::MAP);

        $this->assertEquals($expected, $map->toDB($field));
    }

    public function testForFilter()
    {
        $filter = [
            new FilterItem('id', 'not', 42),
            new FilterItem('id', 'min', 1),
            new FilterItem('bar', '', 'hello'),
            new FilterItem('numbers', 'in', [1, 2, 3]),
            new FilterItem('foo.bar.qux', '', 1),
            new FilterItem('skippy', '', 100),
        ];

        $map = new FieldMap(self::MAP);
        $applyTo = $map->forFilter();

        $iterator = $applyTo($filter);

        $this->assertIsIterable($iterator);
        $mapped = i\iterable_to_array($iterator, true);

        $this->assertCount(6, $mapped);

        $this->assertEquals(new FilterItem('_id', 'not', 42), $mapped[0]);
        $this->assertEquals(new FilterItem('_id', 'min', 1), $mapped[1]);
        $this->assertEquals(new FilterItem('bor', '', 'hello'), $mapped[2]);
        $this->assertSame($filter[3], $mapped[3]);
        $this->assertEquals(new FilterItem('foos.bar.qux', '', 1), $mapped[4]);
        $this->assertSame($filter[5], $mapped[5]);
    }

    public function testForUpdate()
    {
        $instructions = [
            new UpdateInstruction('set', ['id' => 42, 'bar' => 'hello']),
            new UpdateInstruction('inc', ['id' => 1, 'foo.bar.qux' => 9]),
            new UpdateInstruction('set', ['number' => 3]),
        ];

        $map = new FieldMap(self::MAP);
        $applyTo = $map->forUpdate();

        $iterator = $applyTo($instructions);

        $this->assertIsIterable($iterator);
        $mapped = i\iterable_to_array($iterator, true);

        $this->assertCount(3, $mapped);

        $this->assertEquals(new UpdateInstruction('set', ['_id' => 42, 'bor' => 'hello']), $mapped[0]);
        $this->assertEquals(new UpdateInstruction('inc', ['_id' => 1, 'foos.bar.qux' => 9]), $mapped[1]);
        $this->assertSame($instructions[2], $mapped[2]);
    }

    public function testForResult()
    {
        $items = new \ArrayIterator([
            'array' => ['_id' => 42, 'bor' => 'man', 'number' => 3],
            'ArrayObject' => new \ArrayObject(['_id' => 43, 'bor' => 'pop', 'number' => 2]),
            'object' => (object)['_id' => 50, 'bor' => 'kol', 'number' => 1],
            'string' => 'hello',
        ]);

        $map = new FieldMap(self::MAP);
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

        $map = new FieldMap(self::MAP);
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
