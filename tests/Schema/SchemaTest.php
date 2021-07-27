<?php

declare(strict_types=1);

namespace Jasny\Tests\Persist\Schema;

use Jasny\Persist\Exception\NoRelationshipException;
use Jasny\Persist\Map\MapInterface;
use Jasny\Persist\Map\NoMap;
use Jasny\Persist\Map\SchemaMap;
use Jasny\Persist\Schema\Embedded;
use Jasny\Persist\Schema\Relationship;
use Jasny\Persist\Schema\Schema;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * @covers \Jasny\Persist\Schema\Schema
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
            ->withRelationship(new Relationship(Relationship::ONE_TO_MANY, 'foo', 'bar', ['id' => 'fooId']))
            ->withRelationship(new Relationship(Relationship::MANY_TO_MANY, 'bar', 'qux', ['x' => 'y']));

        $this->assertInstanceOf(Schema::class, $schema);
        $this->assertNotSame($this->schema, $schema);

        $this->assertEquals(
            [new Relationship(Relationship::ONE_TO_MANY, 'foo', 'bar', ['id' => 'fooId'])],
            $schema->getRelationships('foo')
        );

        $this->assertEquals(
            [
                new Relationship(Relationship::MANY_TO_ONE, 'bar', 'foo', ['fooId' => 'id']),
                new Relationship(Relationship::MANY_TO_MANY, 'bar', 'qux', ['x' => 'y'])
            ],
            $schema->getRelationships('bar')
        );

        $this->assertEquals(
            [new Relationship(Relationship::MANY_TO_MANY, 'qux', 'bar', ['y' => 'x'])],
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
    public function testWithXToY(string $method, int $typeFoo, int $typeBar)
    {
        /** @var Schema $schema */
        $schema = $this->schema->{$method}('foo', 'bar', ['x' => 'y']);

        $this->assertInstanceOf(Schema::class, $schema);
        $this->assertNotSame($this->schema, $schema);

        $this->assertEquals(
            [new Relationship($typeFoo, 'foo', 'bar', ['x' => 'y'])],
            $schema->getRelationships('foo')
        );

        $this->assertEquals(
            [new Relationship($typeBar, 'bar', 'foo', ['y' => 'x'])],
            $schema->getRelationships('bar')
        );
    }

    public function withEmbeddedProvider()
    {
        return [
            'withOneEmbedded'  => ['withOneEmbedded', Embedded::ONE_TO_ONE],
            'withManyEmbedded' => ['withManyEmbedded', Embedded::ONE_TO_MANY],
        ];
    }

    /**
     * @dataProvider withEmbeddedProvider
     */
    public function testWithEmbedded(string $method, int $type)
    {
        /** @var Schema $schema */
        $schema = $this->schema->{$method}('foo', 'bar');

        $this->assertInstanceOf(Schema::class, $schema);
        $this->assertNotSame($this->schema, $schema);

        $embedded = new Embedded($type, 'foo', 'bar');

        $this->assertEquals([$embedded], $schema->getEmbedded('foo'));
        $this->assertEquals($embedded, $schema->getEmbeddedForField('foo', 'bar'));
    }

    protected function createRelationshipSchema()
    {
        return $this->schema
            ->withOneToMany('foo', 'bar', ['id' => 'fooId'])
            ->withOneToMany('foo', 'qux', ['id' => 'inFoo'])
            ->withOneToMany('foo', 'qux', ['id' => 'outFoo'])
            ->withManyToMany('bar', 'qux', ['x' => 'y'])
            ->withManyEmbedded('foo', 'wos')
            ->withManyToOne('foo.wos', 'qux', ['quxId' => 'id']);
    }

    public function relationshipProvider()
    {
        return [
            'foo - bar' => [
                ['foo', 'bar'],
                new Relationship(Relationship::ONE_TO_MANY, 'foo', 'bar', ['id' => 'fooId']),
            ],
            'bar - foo' => [
                ['bar', 'foo'],
                new Relationship(Relationship::MANY_TO_ONE, 'bar', 'foo', ['fooId' => 'id']),
            ],
            'foo - qux (id = inFoo)' => [
                ['foo', 'qux', ['id' => 'inFoo']],
                new Relationship(Relationship::ONE_TO_MANY, 'foo', 'qux', ['id' => 'inFoo']),
            ],
            'foo - qux (id = outFoo)' => [
                ['foo', 'qux', ['id' => 'outFoo']],
                new Relationship(Relationship::ONE_TO_MANY, 'foo', 'qux', ['id' => 'outFoo']),
            ],
            'foo.wos - qux' => [
                ['foo.wos', 'qux'],
                new Relationship(Relationship::MANY_TO_ONE, 'foo.wos', 'qux', ['quxId' => 'id']),
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
            'foo - pum' => [
                ['foo', 'pum'],
                'No relationship found between foo and pum',
            ],
            'foo - qux' => [
                ['foo', 'qux'],
                'Multiple relationships found between foo and qux',
            ],
            'foo - bar (id = x)' => [
                ['foo', 'bar', ['id' => 'x']],
                'No relationship found between foo and bar with (foo.id = bar.x)',
            ],
        ];
    }

    /**
     * @dataProvider relationshipExceptionProvider
     */
    public function testRelationshipException(array $args, string $message)
    {
        $schema = $this->createRelationshipSchema();

        $this->expectException(NoRelationshipException::class);
        $this->expectExceptionMessage($message);

        $schema->getRelationship(...$args);
    }

    public function relationshipForFieldProvider()
    {
        return [
            'bar.fooId' => [
                ['bar', 'fooId'],
                new Relationship(Relationship::MANY_TO_ONE, 'bar', 'foo', ['fooId' => 'id']),
            ],
            'qux.inFoo' => [
                ['qux', 'inFoo'],
                new Relationship(Relationship::MANY_TO_ONE, 'qux', 'foo', ['inFoo' => 'id']),
            ],
        ];
    }

    /**
     * @dataProvider relationshipForFieldProvider
     */
    public function testRelationshipForField(array $args, Relationship $expected)
    {
        $schema = $this->createRelationshipSchema();

        $relationship = $schema->getRelationshipForField(...$args);
        $this->assertEquals($expected, $relationship);
    }

    public function relationshipForFieldExceptionProvider()
    {
        return [
            'foo.x' => [
                ['foo', 'x'],
                "No relationship found for field 'x' of 'foo'",
            ],
            'foo.id' => [
                ['foo', 'id'],
                "Multiple relationships found for field 'id' of 'foo'",
            ],
            'foo.wos' => [
                ['foo', 'wos'],
                "No relationship found for field 'wos' of 'foo'",
            ],
        ];
    }

    /**
     * @dataProvider relationshipForFieldExceptionProvider
     */
    public function testRelationshipForFieldException(array $args, string $message)
    {
        $schema = $this->createRelationshipSchema();

        $this->expectException(NoRelationshipException::class);
        $this->expectExceptionMessage($message);

        $schema->getRelationshipForField(...$args);
    }
}
