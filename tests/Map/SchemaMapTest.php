<?php

declare(strict_types=1);

namespace Jasny\DB\Tests\Map;

use Jasny\DB\Map\ChildMap;
use Jasny\DB\Map\MapInterface;
use Jasny\DB\Map\NestedMap;
use Jasny\DB\Map\SchemaMap;
use Jasny\DB\Schema\Relationship;
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


    public function testWithRelated(): void
    {
        $schema = (new Schema())
            ->withMap('foo', $this->createMock(MapInterface::class))
            ->withMap('bar', $this->createMock(MapInterface::class))
            ->withRelationship(new Relationship(Relationship::MANY_TO_ONE, 'foo', 'barId', 'bar', 'id'));

        $map = new SchemaMap('foo', $schema);
        $this->assertSame($schema->getMapOf('foo'), $map->getInner());

        $mapWithBar = $map->withRelated('barId');

        $this->assertInstanceOf(NestedMap::class, $mapWithBar->getInner());
        $nested = $mapWithBar->getInner()->getInner();

        $this->assertIsArray($nested);
        $this->assertArrayHasKey('', $nested);
        $this->assertSame($schema->getMapOf('foo'), $nested['']);

        $this->assertArrayHasKey('barId', $nested);
        $this->assertInstanceOf(ChildMap::class, $nested['barId']);
        $this->assertSame('barId', $nested['barId']->getField());
        $this->assertSame($schema->getMapOf('bar'), $nested['barId']->getInner());
    }
}
