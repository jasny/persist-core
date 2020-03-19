<?php

declare(strict_types=1);

namespace Jasny\DB\Tests\Schema;

use Jasny\DB\Map\MapInterface;
use Jasny\DB\Map\NoMap;
use Jasny\DB\Map\SchemaMap;
use Jasny\DB\Schema\Relationship;
use Jasny\DB\Schema\Schema;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * @covers \Jasny\DB\Schema\Schema
 */
class SchemaTest extends TestCase
{
    /** @var MapInterface&MockObject */
    protected $fooMap;

    /** @var MapInterface&MockObject */
    protected $barMap;

    /** @var MapInterface&MockObject */
    protected $quxMap;

    protected Schema $schema;

    public function setUp(): void
    {
        $this->fooMap = $this->createMock(MapInterface::class);
        $this->barMap = $this->createMock(MapInterface::class);
        $this->quxMap = $this->createMock(MapInterface::class);

        $this->schema = (new Schema())
            ->withMap('foo', $this->fooMap)
            ->withMap('bar', $this->barMap)
            ->withMap('qux', $this->quxMap);
    }

    public function testGetMapOf()
    {
        $this->assertSame($this->fooMap, $this->schema->getMapOf('foo'));
        $this->assertSame($this->barMap, $this->schema->getMapOf('bar'));
        $this->assertInstanceOf(NoMap::class, $this->schema->getMapOf('pum'));
    }

    public function testMap()
    {
        $foo = $this->schema->map('foo');
        $this->assertInstanceOf(SchemaMap::class, $foo);
        $this->assertSame($this->fooMap, $foo->getInner());

        $bar = $this->schema->map('bar');
        $this->assertInstanceOf(SchemaMap::class, $bar);
        $this->assertSame($this->barMap, $bar->getInner());

        $pum = $this->schema->map('pum');
        $this->assertInstanceOf(SchemaMap::class, $pum);
        $this->assertInstanceOf(NoMap::class, $pum->getInner());
    }

    public function testWithDefaultMap()
    {
        $default = $this->createMock(MapInterface::class);
        $schema = $this->schema->withDefaultMap($default);

        $this->assertNotSame($this->schema, $schema);

        $this->assertSame($default, $schema->getMapOf('pum'));

        $pum = $schema->map('pum');
        $this->assertInstanceOf(SchemaMap::class, $pum);
        $this->assertSame($default, $pum->getInner());
    }


    public function testWithRelationship()
    {
        $schema = $this->schema
            ->withRelationship(new Relationship(Relationship::ONE_TO_MANY, 'foo', 'id', 'bar', 'fooId'))
            ->withRelationship(new Relationship(Relationship::MANY_TO_MANY, 'bar', 'x', 'qux', 'y'));

        $this->assertInstanceOf(Schema::class, $schema);
        $this->assertNotSame($this->schema, $schema);

        $this->assertEquals(
            [new Relationship(Relationship::ONE_TO_MANY, 'foo', 'id', 'bar', 'fooId')],
            $schema->getRelationships('foo')
        );

        $this->assertEquals(
            [
                new Relationship(Relationship::MANY_TO_ONE, 'bar', 'fooId', 'foo', 'id'),
                new Relationship(Relationship::MANY_TO_MANY, 'bar', 'x', 'qux', 'y')
            ],
            $schema->getRelationships('bar')
        );

        $this->assertEquals(
            [new Relationship(Relationship::MANY_TO_MANY, 'qux', 'y', 'bar', 'x')],
            $schema->getRelationships('qux')
        );

        $this->assertEquals([], $schema->getRelationships('pum'));
    }

    public function withXToYProvider()
    {
        return [
            'withOneToOne'   => ['withOneToOne', Relationship::ONE_TO_ONE, Relationship::ONE_TO_ONE],
            'withOneToMany'  => ['withOneToMany', Relationship::ONE_TO_MANY, Relationship::MANY_TO_ONE],
            'withManyToOne'  => ['withManyToOne', Relationship::MANY_TO_ONE, Relationship::ONE_TO_MANY],
            'withManyToMany' => ['withManyToMany', Relationship::MANY_TO_MANY, Relationship::MANY_TO_MANY],
        ];
    }

    /**
     * @dataProvider withXToYProvider
     */
    public function testWithXtoY(string $method, int $typeFoo, int $typeBar)
    {
        /** @var Schema $schema */
        $schema = $this->schema->{$method}('foo', 'x', 'bar', 'y');

        $this->assertInstanceOf(Schema::class, $schema);
        $this->assertNotSame($this->schema, $schema);

        $this->assertEquals(
            [new Relationship($typeFoo, 'foo', 'x', 'bar', 'y')],
            $schema->getRelationships('foo')
        );

        $this->assertEquals(
            [new Relationship($typeBar, 'bar', 'y', 'foo', 'x')],
            $schema->getRelationships('bar')
        );
    }

    protected function createRelationshipSchema()
    {
        return $this->schema
            ->withRelationship(new Relationship(Relationship::ONE_TO_MANY, 'foo', 'id', 'bar', 'fooId'))
            ->withRelationship(new Relationship(Relationship::ONE_TO_MANY, 'foo', 'id', 'qux', 'inFoo'))
            ->withRelationship(new Relationship(Relationship::ONE_TO_MANY, 'foo', 'id', 'qux', 'outFoo'))
            ->withRelationship(new Relationship(Relationship::MANY_TO_MANY, 'bar', 'x', 'qux', 'y'));
    }

    public function relationshipProvider()
    {
        return [
            'foo - bar' => [
                ['foo', null, 'bar', null],
                new Relationship(Relationship::ONE_TO_MANY, 'foo', 'id', 'bar', 'fooId'),
            ],
            'bar - foo' => [
                ['bar', null, 'foo', null],
                new Relationship(Relationship::MANY_TO_ONE, 'bar', 'fooId', 'foo', 'id'),
            ],
            'foo - qux (inFoo)' => [
                ['foo', null, 'qux', 'inFoo'],
                new Relationship(Relationship::ONE_TO_MANY, 'foo', 'id', 'qux', 'inFoo'),
            ],
            'foo - qux (outFoo)' => [
                ['foo', null, 'qux', 'outFoo'],
                new Relationship(Relationship::ONE_TO_MANY, 'foo', 'id', 'qux', 'outFoo'),
            ],
        ];
    }

    /**
     * @dataProvider relationshipProvider
     */
    public function testGetRelationship(array $args, Relationship $expected)
    {
        $schema = $this->createRelationshipSchema();

        $relationship = $schema->getRelationship(...$args);
        $this->assertEquals($expected, $relationship);
    }

    public function relationshipExceptionProvider()
    {
        return [
            'pum' => [
                ['pum', null, null, null],
                'No relationship found for pum',
            ],
            'foo - pum' => [
                ['foo', null, 'pum', null],
                'No relationship found between foo and pum',
            ],
            'foo - qux' => [
                ['foo', null, 'qux', null],
                'Multiple relationships found between foo and qux',
            ],
            'foo (x)' => [
                ['foo', 'x', null, null],
                'No relationship found for foo (x)',
            ],
            'foo - bar (x)' => [
                ['foo', null, 'bar', 'x'],
                'No relationship found between foo and bar (x)',
            ],
        ];
    }

    /**
     * @dataProvider relationshipExceptionProvider
     */
    public function testRelationshipException(array $args, string $message)
    {
        $schema = $this->createRelationshipSchema();

        $this->expectException(\UnexpectedValueException::class);
        $this->expectExceptionMessage($message);

        $schema->getRelationship(...$args);
    }

    public function testGetRelationshipInvalidArgument()
    {
        $schema = $this->createRelationshipSchema();

        $this->expectException(\InvalidArgumentException::class);

        $schema->getRelationship('foo', null, null, 'id');
    }
}
