<?php

declare(strict_types=1);

namespace Jasny\Persist\Tests\Map;

use Improved\IteratorPipeline\Pipeline;
use Jasny\Persist\Map\FieldMap;
use PHPUnit\Framework\TestCase;

/**
 * @covers \Jasny\Persist\Map\FieldMap
 */
class FieldMapTest extends TestCase
{
    protected const MAP = [
        'id' => '_id',
        'foo' => 'foos',
        'bar' => 'bor',
        'skippy' => false
    ];

    protected FieldMap $map;

    public function setUp(): void
    {
        $this->map = new FieldMap(self::MAP);
    }

    public function fieldProvider()
    {
        return [
            'id' => ['id', '_id'],
            'foo' => ['foo', 'foos'],
            'bar.xy' => ['bar.xy', 'bor.xy'],
            'foo.bar.qux' => ['foo.bar.qux', 'foos.bar.qux'],
            'numbers' => ['numbers', null],
            'skippy' => ['skippy', false],
        ];
    }

    public function testWithOpts()
    {
        $this->assertSame($this->map, $this->map->withOpts([]));
    }

    /**
     * @dataProvider fieldProvider
     */
    public function testApplyToField(string $field, $expected)
    {
        $this->map = new FieldMap(self::MAP);
        $this->assertEquals($expected, $this->map->applyToField($field));
    }

    public function testApply()
    {
        $items = [
            'array' => ['id' => 42, 'bar' => 'man', 'number' => 3],
            'ArrayObject' => new \ArrayObject(['id' => 43, 'bar' => 'pop', 'number' => 2]),
            'object' => (object)['id' => 50, 'bar' => 'kol', 'number' => 1],
        ];

        $mapped = Pipeline::with($items)
            ->map(fn($item) => $this->map->apply($item))
            ->toArray();

        $this->assertArrayHasKey('array', $mapped);
        $this->assertEquals(['_id' => 42, 'bor' => 'man', 'number' => 3], $mapped['array']);
        $this->assertNotEquals($items['array'], $mapped['array']);

        $this->assertArrayHasKey('ArrayObject', $mapped);
        $this->assertInstanceOf(\ArrayObject::class, $mapped['ArrayObject']);
        $this->assertEquals(
            ['_id' => 43, 'bor' => 'pop', 'number' => 2],
            $mapped['ArrayObject']->getArrayCopy()
        );
        $this->assertNotEquals($items['ArrayObject'], $mapped['ArrayObject']);

        $this->assertArrayHasKey('object', $mapped);
        $this->assertEquals((object)['_id' => 50, 'bor' => 'kol', 'number' => 1], $mapped['object']);
        $this->assertNotEquals($items['object'], $mapped['object']);

        $this->assertCount(3, $mapped);
    }

    public function testApplyInverse()
    {
        $items = [
            'array' => ['_id' => 42, 'bor' => 'man', 'number' => 3],
            'ArrayObject' => new \ArrayObject(['_id' => 43, 'bor' => 'pop', 'number' => 2]),
            'object' => (object)['_id' => 50, 'bor' => 'kol', 'number' => 1],
        ];

        $mapped = Pipeline::with($items)
            ->map(fn($item) => $this->map->applyInverse($item))
            ->toArray();

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

        $this->assertCount(3, $mapped);
    }
}
