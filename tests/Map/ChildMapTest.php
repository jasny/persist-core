<?php

declare(strict_types=1);

namespace Persist\Tests\Map;

use Improved\IteratorPipeline\Pipeline;
use Persist\Map\ChildMap;
use Persist\Map\FieldMap;
use Persist\Map\MapInterface;
use PHPUnit\Framework\TestCase;

/**
 * @covers \Persist\Map\ChildMap
 */
class ChildMapTest extends TestCase
{
    public function testConstructWithMap()
    {
        $inner = $this->createMock(MapInterface::class);
        $child = new ChildMap('one.color', $inner);

        $this->assertEquals('one.color', $child->getField());
        $this->assertFalse($child->isForMany());
        $this->assertSame($inner, $child->getInner());
    }

    public function testConstructWithArray()
    {
        $child = new ChildMap('two[]', ['foo' => 'oof']);

        $this->assertEquals('two', $child->getField());
        $this->assertTrue($child->isForMany());

        $this->assertInstanceOf(FieldMap::class, $child->getInner());
        $this->assertEquals('oof', $child->getInner()->applyToField('foo'));
    }

    public function testWithOpts()
    {
        $inner = $this->createMock(MapInterface::class);
        $child = new ChildMap('one.color', $inner);

        $this->assertSame($child, $child->withOpts([]));
    }

    public function testApplyToField()
    {
        $inner = $this->createMock(MapInterface::class);
        $child = new ChildMap('one.color', $inner);

        $inner->expects($this->exactly(3))->method('applyToField')
            ->withConsecutive(['red'], ['green'], ['blue'])
            ->willReturnOnConsecutiveCalls(null, false, 'azul');

        $this->assertNull($child->applyToField('one.color.red'));
        $this->assertFalse($child->applyToField('one.color.green'));
        $this->assertEquals('one.color.azul', $child->applyToField('one.color.blue'));

        $this->assertNull($child->applyToField('one'));
        $this->assertNull($child->applyToField('two'));
        $this->assertNull($child->applyToField('one.color'));
    }

    public function testApply()
    {
        $child = new ChildMap('one.color', ['green' => false, 'blue' => 'azul']);

        $items = [
            'array' => ['id' => 42, 'one' => ['color' => ['red' => 1, 'green' => 2, 'blue' => 3]]],
            'ArrayObject' => new \ArrayObject([
                'id' => 43,
                'one' => new \ArrayObject([
                    'color' => new \ArrayObject(['red' => 11, 'green' => 12, 'blue' => 13]),
                ]),
            ]),
            'object' => (object)['id' => 50, 'one' => ['color' => ['red' => 21, 'green' => 22, 'blue' => 23]]],
            'same' => (object)['id' => 99, 'one' => ['color' => ['red' => 31]]],
            'nop' => (object)['id' => 99, 'one' => null],
        ];

        $mapped = Pipeline::with($items)
            ->map(fn($item) => $child->apply($item))
            ->toArray();

        $this->assertArrayHasKey('array', $mapped);
        $this->assertEquals(['id' => 42, 'one' => ['color' => ['red' => 1, 'azul' => 3]]], $mapped['array']);

        $this->assertArrayHasKey('ArrayObject', $mapped);
        $this->assertInstanceOf(\ArrayObject::class, $mapped['ArrayObject']);
        $this->assertEquals(
            ['id' => 43, 'one' => new \ArrayObject(['color' => new \ArrayObject(['red' => 11, 'azul' => 13])])],
            $mapped['ArrayObject']->getArrayCopy()
        );

        $this->assertArrayHasKey('object', $mapped);
        $this->assertEquals((object)['id' => 50, 'one' => ['color' => ['red' => 21, 'azul' => 23]]], $mapped['object']);

        $this->assertArrayHasKey('same', $mapped);
        $this->assertSame($items['same'], $mapped['same']);

        $this->assertArrayHasKey('nop', $mapped);
        $this->assertSame($items['nop'], $mapped['nop']);

        $this->assertCount(5, $mapped);
    }

    public function testApplyToMany()
    {
        $child = new ChildMap('colors[]', ['green' => false, 'blue' => 'azul']);

        $items = [
            'array' => [
                'id' => 42,
                'colors' => [
                    ['red' => 1, 'green' => 2, 'blue' => 3],
                    ['red' => 9, 'green' => 8, 'blue' => 7],
                    ['red' => 5],
                ],
            ],
            'ArrayObject' => new \ArrayObject([
                'id' => 43,
                'colors' => new \ArrayObject([
                    new \ArrayObject(['red' => 11, 'green' => 12, 'blue' => 13]),
                    new \ArrayObject(['red' => 19, 'green' => 18, 'blue' => 17]),
                ]),
            ]),
            'object' => (object)[
                'id' => 50,
                'colors' => [
                    ['red' => 21, 'green' => 22, 'blue' => 23],
                ],
            ],
            'empty' => (object)[
                'id' => 99,
                'colors' => [],
            ],
            'nop' => (object)['id' => 99, 'colors' => null],
        ];

        $mapped = Pipeline::with($items)
            ->map(fn($item) => $child->apply($item))
            ->toArray();

        $this->assertArrayHasKey('array', $mapped);
        $this->assertEquals([
            'id' => 42,
            'colors' => [
                ['red' => 1, 'azul' => 3],
                ['red' => 9, 'azul' => 7],
                ['red' => 5],
            ],
        ], $mapped['array']);

        $this->assertArrayHasKey('ArrayObject', $mapped);
        $this->assertInstanceOf(\ArrayObject::class, $mapped['ArrayObject']);
        $this->assertEquals([
            'id' => 43,
            'colors' => new \ArrayObject([
                new \ArrayObject(['red' => 11, 'azul' => 13]),
                new \ArrayObject(['red' => 19, 'azul' => 17]),
            ]),
        ], $mapped['ArrayObject']->getArrayCopy());

        $this->assertArrayHasKey('object', $mapped);
        $this->assertEquals((object)[
            'id' => 50,
            'colors' => [
                ['red' => 21, 'azul' => 23],
            ],
        ], $mapped['object']);

        $this->assertArrayHasKey('empty', $mapped);
        $this->assertSame($items['empty'], $mapped['empty']);

        $this->assertArrayHasKey('nop', $mapped);
        $this->assertSame($items['nop'], $mapped['nop']);

        $this->assertCount(5, $mapped);
    }

    public function testApplyInverse()
    {
        $child = new ChildMap('one.color', ['green' => false, 'blue' => 'azul']);

        $items = [
            'array' => ['id' => 42, 'one' => ['color' => ['red' => 1, 'azul' => 3]]],
            'ArrayObject' => new \ArrayObject([
                'id' => 43,
                'one' => new \ArrayObject([
                    'color' => new \ArrayObject(['red' => 11, 'azul' => 13]),
                ]),
            ]),
            'object' => (object)['id' => 50, 'one' => ['color' => ['red' => 21, 'azul' => 23]]],
            'same' => (object)['id' => 99, 'one' => ['color' => ['red' => 31]]],
            'nop' => (object)['id' => 99, 'one' => null],
        ];

        $mapped = Pipeline::with($items)
            ->map(fn($item) => $child->applyInverse($item))
            ->toArray();

        $this->assertArrayHasKey('array', $mapped);
        $this->assertEquals(['id' => 42, 'one' => ['color' => ['red' => 1, 'blue' => 3]]], $mapped['array']);

        $this->assertArrayHasKey('ArrayObject', $mapped);
        $this->assertInstanceOf(\ArrayObject::class, $mapped['ArrayObject']);
        $this->assertEquals(
            ['id' => 43, 'one' => new \ArrayObject(['color' => new \ArrayObject(['red' => 11, 'blue' => 13])])],
            $mapped['ArrayObject']->getArrayCopy(),
        );

        $this->assertArrayHasKey('object', $mapped);
        $this->assertEquals((object)['id' => 50, 'one' => ['color' => ['red' => 21, 'blue' => 23]]], $mapped['object']);

        $this->assertArrayHasKey('same', $mapped);
        $this->assertSame($items['same'], $mapped['same']);

        $this->assertArrayHasKey('nop', $mapped);
        $this->assertSame($items['nop'], $mapped['nop']);

        $this->assertCount(5, $mapped);
    }

    public function testApplyInverseToMany()
    {
        $child = new ChildMap('colors[]', ['green' => false, 'blue' => 'azul']);

        $items = [
            'array' => [
                'id' => 42,
                'colors' => [
                    ['red' => 1, 'azul' => 3],
                    ['red' => 9, 'azul' => 7],
                    ['red' => 5],
                ],
            ],
            'ArrayObject' => new \ArrayObject([
                'id' => 43,
                'colors' => new \ArrayObject([
                    new \ArrayObject(['red' => 11, 'azul' => 13]),
                    new \ArrayObject(['red' => 19, 'azul' => 17]),
                ]),
            ]),
            'object' => (object)[
                'id' => 50,
                'colors' => [
                    ['red' => 21, 'azul' => 23],
                ],
            ],
            'empty' => (object)[
                'id' => 99,
                'colors' => [],
            ],
            'nop' => (object)['id' => 99, 'colors' => null],
        ];

        $mapped = Pipeline::with($items)
            ->map(fn($item) => $child->applyInverse($item))
            ->toArray();

        $this->assertArrayHasKey('array', $mapped);
        $this->assertEquals([
            'id' => 42,
            'colors' => [
                ['red' => 1, 'blue' => 3],
                ['red' => 9, 'blue' => 7],
                ['red' => 5],
            ],
        ], $mapped['array']);

        $this->assertArrayHasKey('ArrayObject', $mapped);
        $this->assertInstanceOf(\ArrayObject::class, $mapped['ArrayObject']);
        $this->assertEquals([
            'id' => 43,
            'colors' => new \ArrayObject([
                new \ArrayObject(['red' => 11, 'blue' => 13]),
                new \ArrayObject(['red' => 19, 'blue' => 17]),
            ]),
        ], $mapped['ArrayObject']->getArrayCopy());

        $this->assertArrayHasKey('object', $mapped);
        $this->assertEquals((object)[
            'id' => 50,
            'colors' => [
                ['red' => 21, 'blue' => 23],
            ],
        ], $mapped['object']);

        $this->assertArrayHasKey('empty', $mapped);
        $this->assertSame($items['empty'], $mapped['empty']);

        $this->assertArrayHasKey('nop', $mapped);
        $this->assertSame($items['nop'], $mapped['nop']);

        $this->assertCount(5, $mapped);
    }
}
