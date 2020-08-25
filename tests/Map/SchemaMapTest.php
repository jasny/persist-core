<?php

declare(strict_types=1);

namespace Jasny\DB\Tests\Map;

use Jasny\DB\Map\ChildMap;
use Jasny\DB\Map\MapInterface;
use Jasny\DB\Map\NestedMap;
use Jasny\DB\Map\NoMap;
use Jasny\DB\Map\SchemaMap;
use Jasny\DB\Option\Functions as opt;
use Jasny\DB\Option\OptionInterface;
use Jasny\DB\Schema\Schema;
use Jasny\DB\Schema\SchemaInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * @covers \Jasny\DB\Map\SchemaMap
 * @covers \Jasny\DB\Map\Traits\ProxyTrait
 */
class SchemaMapTest extends TestCase
{
    /** @var SchemaInterface&MockObject */
    protected $schema;

    /** @var MapInterface&MockObject */
    protected $inner;

    protected SchemaMap $map;

    public function setUp(): void
    {
        $this->schema = $this->createMock(SchemaInterface::class);
        $this->inner = $this->createMock(MapInterface::class);

        $this->schema->expects($this->never())->method('map');
        $this->schema->expects($this->once())->method('getMapOf')
            ->with('foo')
            ->willReturn($this->inner);

        $this->map = new SchemaMap('foo', $this->schema);
    }

    public function testApplyToField(): void
    {
        $this->inner->expects($this->once())
            ->method('applyToField')
            ->with('foo')
            ->willReturn('oof');

        $this->assertEquals('oof', $this->map->applyToField('foo'));
    }

    public function testApply(): void
    {
        $this->inner->expects($this->once())->method('apply')
            ->with(['foo' => 2])
            ->willReturn(['oof' => 2]);

        $this->assertEquals(['oof' => 2], $this->map->apply(['foo' => 2]));
    }

    public function testApplyInverse(): void
    {
        $this->inner->expects($this->once())
            ->method('applyInverse')
            ->with(['oof' => 2])
            ->willReturn(['foo' => 2]);

        $this->assertEquals(['foo' => 2], $this->map->applyInverse(['oof' => 2]));
    }

    public function testGetInner(): void
    {
        $this->assertSame($this->inner, $this->map->getInner());
    }


    public function testWithOpts(): void
    {
        $fooMap = $this->createMock(MapInterface::class);
        $barMap = $this->createMock(MapInterface::class);
        $barMapWithOpts = $this->createMock(MapInterface::class);

        $schema = (new Schema())
            ->withMap('foo', $fooMap)
            ->withMap('bar', $barMap)
            ->withManyToOne('foo', 'bar', ['our_bar' => 'id'])
            ->withOneToOne('foo:default', 'bar', ['id' => 'default_foo']);

        $map = new SchemaMap('bar', $schema);
        $this->assertSame($schema->getMapOf('bar'), $map->getInner());

        $opts = [
            $this->createMock(OptionInterface::class),
            opt\lookup('foo')->as('our_foos'),
            opt\hydrate('default_foo'),
        ];

        $barMap->expects($this->once())
            ->method('withOpts')
            ->with($opts)
            ->willReturn($barMapWithOpts);

        $mapWithBar = $map->withOpts($opts);

        $this->assertInstanceOf(NestedMap::class, $mapWithBar->getInner());

        /** @var MapInterface[] $nested */
        $nested = $mapWithBar->getInner()->getInner();
        $this->assertIsArray($nested);
        $this->assertContainsOnlyInstancesOf(MapInterface::class, $nested);

        $this->assertIsArray($nested);
        $this->assertArrayHasKey('', $nested);
        $this->assertSame($barMapWithOpts, $nested['']);

        $this->assertArrayHasKey('our_foos', $nested);
        $this->assertInstanceOf(ChildMap::class, $nested['our_foos']);
        $this->assertSame('our_foos', $nested['our_foos']->getField());
        $this->assertTrue($nested['our_foos']->isForMany());
        $this->assertSame($fooMap, $nested['our_foos']->getInner());

        $this->assertArrayHasKey('default_foo', $nested);
        $this->assertInstanceOf(ChildMap::class, $nested['default_foo']);
        $this->assertSame('default_foo', $nested['default_foo']->getField());
        $this->assertFalse($nested['default_foo']->isForMany());
        $this->assertSame($fooMap, $nested['default_foo']->getInner());
    }

    public function testWithOptsInnerSelf(): void
    {
        $fooMap = $this->createMock(MapInterface::class);
        $barMap = $this->createMock(MapInterface::class);

        $schema = (new Schema())
            ->withMap('foo', $fooMap)
            ->withMap('bar', $barMap)
            ->withManyToOne('foo', 'bar', ['our_bar' => 'id'])
            ->withOneToOne('foo:default', 'bar', ['id' => 'default_foo']);

        $map = new SchemaMap('bar', $schema);
        $this->assertSame($schema->getMapOf('bar'), $map->getInner());

        $opts = [
            $this->createMock(OptionInterface::class),
            opt\lookup('foo')->as('our_foos'),
            opt\hydrate('default_foo'),
        ];

        $barMap->expects($this->once())
            ->method('withOpts')
            ->with($opts)
            ->willReturnSelf();

        $mapWithBar = $map->withOpts($opts);

        $this->assertInstanceOf(NestedMap::class, $mapWithBar->getInner());

        /** @var MapInterface[] $nested */
        $nested = $mapWithBar->getInner()->getInner();
        $this->assertIsArray($nested);
        $this->assertContainsOnlyInstancesOf(MapInterface::class, $nested);

        $this->assertIsArray($nested);
        $this->assertArrayHasKey('', $nested);
        $this->assertSame($barMap, $nested['']);
    }

    public function nestedLookupOptsProvider(): array
    {
        return [
            'with' => [
                $this->createMock(OptionInterface::class),
                opt\lookup('foo')->as('our_foos')->with(
                    opt\lookup('qux')->with(
                        opt\lookup('pip'),
                        opt\lookup('rop'),
                    ),
                ),
            ],
            'with / for' => [
                $this->createMock(OptionInterface::class),
                opt\lookup('foo')->as('our_foos')->with(
                    opt\lookup('qux'),
                    opt\lookup('pip')->for('qux'),
                    opt\lookup('rop')->for('qux'),
                ),
            ],
            'for' => [
                $this->createMock(OptionInterface::class),
                opt\lookup('foo')->as('our_foos'),
                opt\lookup('qux')->for('our_foos'),
                opt\lookup('pip')->for('our_foos.qux'),
                opt\lookup('rop')->for('our_foos.qux'),
            ],
        ];
    }

    /**
     * @dataProvider nestedLookupOptsProvider
     */
    public function testWithOptsWithNestedLookup(OptionInterface ...$opts): void
    {
        $fooMap = $this->createMock(MapInterface::class);
        $quxMap = $this->createMock(MapInterface::class);
        $barMap = $this->createMock(MapInterface::class);
        $pipMap = $this->createMock(MapInterface::class);
        $barMapWithOpts = $this->createMock(MapInterface::class);

        $schema = (new Schema())
            ->withMap('foo', $fooMap)
            ->withMap('qux', $quxMap)
            ->withMap('bar', $barMap)
            ->withMap('pip', $pipMap)
            ->withManyToOne('foo', 'bar', ['our_bar' => 'id'])
            ->withManyToOne('qux', 'foo', ['foo_id' => 'id'])
            ->withOneToOne('qux', 'pip', ['pip_id' => 'id'])
            ->withOneToOne('qux', 'rop', ['rop_id' => 'id'])
            ->withOneToOne('foo:default', 'bar', ['id' => 'default_foo']);

        $map = new SchemaMap('bar', $schema);
        $this->assertSame($schema->getMapOf('bar'), $map->getInner());

        $barMap->expects($this->once())
            ->method('withOpts')
            ->with($opts)
            ->willReturn($barMapWithOpts);

        $mapWithBar = $map->withOpts($opts);

        $this->assertInstanceOf(NestedMap::class, $mapWithBar->getInner());

        $nested = $mapWithBar->getInner()->getInner();
        $this->assertIsArray($nested);
        $this->assertContainsOnlyInstancesOf(MapInterface::class, $nested);

        $this->assertIsArray($nested);
        $this->assertArrayHasKey('', $nested);
        $this->assertSame($barMapWithOpts, $nested['']);

        $this->assertArrayHasKey('our_foos', $nested);
        $this->assertInstanceOf(ChildMap::class, $nested['our_foos']);
        $this->assertEquals('our_foos', $nested['our_foos']->getField());
        $this->assertTrue($nested['our_foos']->isForMany());

        $this->assertInstanceOf(NestedMap::class, $nested['our_foos']->getInner());

        $nestedFoo = $nested['our_foos']->getInner()->getInner();
        $this->assertSame($fooMap, $nestedFoo['']);

        $this->assertArrayHasKey('qux', $nestedFoo);
        $this->assertInstanceOf(ChildMap::class, $nestedFoo['qux']);
        $this->assertEquals('qux', $nestedFoo['qux']->getField());
        $this->assertTrue($nestedFoo['qux']->isForMany());

        $this->assertInstanceOf(NestedMap::class, $nestedFoo['qux']->getInner());

        $nestedQux = $nestedFoo['qux']->getInner()->getInner();
        $this->assertSame($quxMap, $nestedQux['']);

        $this->assertArrayHasKey('pip', $nestedQux);
        $this->assertSame($pipMap, $nestedQux['pip']->getInner());

        // There isn't a map for 'rop', so no nested map is applied.
        $this->assertArrayNotHasKey('rop', $nestedQux);
    }
}
