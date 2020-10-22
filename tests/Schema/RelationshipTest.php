<?php

declare(strict_types=1);

namespace Jasny\Tests\Persist\Schema;

use Jasny\Persist\Schema\Relationship;
use PHPUnit\Framework\TestCase;

/**
 * @covers \Jasny\Persist\Schema\Relationship
 */
class RelationshipTest extends TestCase
{
    protected Relationship $oneToOne;
    protected Relationship $oneToMany;
    protected Relationship $manyToOne;
    protected Relationship $manyToMany;

    public function setUp(): void
    {
        $this->oneToOne = new Relationship(Relationship::ONE_TO_ONE, 'foo', 'bar', ['x' => 'y']);
        $this->oneToMany = new Relationship(Relationship::ONE_TO_MANY, 'foo', 'bar', ['x' => 'y']);
        $this->manyToOne = new Relationship(Relationship::MANY_TO_ONE, 'foo', 'bar', ['x' => 'y']);
        $this->manyToMany = new Relationship(Relationship::MANY_TO_MANY, 'foo', 'bar', ['x' => 'y']);
    }

    public function testGetType()
    {
        $this->assertEquals(Relationship::ONE_TO_ONE, $this->oneToOne->getType());
        $this->assertEquals(Relationship::ONE_TO_MANY, $this->oneToMany->getType());
        $this->assertEquals(Relationship::MANY_TO_ONE, $this->manyToOne->getType());
        $this->assertEquals(Relationship::MANY_TO_MANY, $this->manyToMany->getType());
    }

    public function testIsFromMany()
    {
        $this->assertFalse($this->oneToOne->isFromMany());
        $this->assertFalse($this->oneToMany->isFromMany());
        $this->assertTrue($this->manyToOne->isFromMany());
        $this->assertTrue($this->manyToMany->isFromMany());
    }

    public function testIsToMany()
    {
        $this->assertFalse($this->oneToOne->isToMany());
        $this->assertTrue($this->oneToMany->isToMany());
        $this->assertFalse($this->manyToOne->isToMany());
        $this->assertTrue($this->manyToMany->isToMany());
    }


    public function testGet()
    {
        $relationship = new Relationship(Relationship::ONE_TO_ONE, 'foo', 'bar', ['x' => 'y']);

        $this->assertEquals('foo', $relationship->getCollection());
        $this->assertEquals('bar', $relationship->getRelatedCollection());
        $this->assertEquals(['x' => 'y'], $relationship->getMatch());
    }

    public function swappedProvider()
    {
        return [
            'one to one'   => [Relationship::ONE_TO_ONE, Relationship::ONE_TO_ONE],
            'one to many'  => [Relationship::ONE_TO_MANY, Relationship::MANY_TO_ONE],
            'many to one'  => [Relationship::MANY_TO_ONE, Relationship::ONE_TO_MANY],
            'many to many' => [Relationship::MANY_TO_MANY, Relationship::MANY_TO_MANY],
        ];
    }

    /**
     * @dataProvider swappedProvider
     */
    public function testSwapped(int $type, int $expectedType)
    {
        $relationship = new Relationship($type, 'foo', 'bar', ['x' => 'y']);
        $swapped = $relationship->swapped();

        $this->assertEquals($expectedType, $swapped->getType());

        $this->assertEquals('bar', $swapped->getCollection());
        $this->assertEquals('foo', $swapped->getRelatedCollection());
        $this->assertEquals(['y' => 'x'], $swapped->getMatch());
    }

    public function testMatches()
    {
        $this->assertTrue($this->oneToOne->matches('foo', 'bar', null));
        $this->assertTrue($this->oneToOne->matches('foo', 'bar', ['x' => 'y']));

        $this->assertFalse($this->oneToOne->matches('foo', 'qux', null));
        $this->assertFalse($this->oneToOne->matches('foo', 'bar', ['a' => 'b']));
    }

    public function testInvalidTypeInConstructor()
    {
        $this->expectException(\InvalidArgumentException::class);

        new Relationship(9999, 'foo', 'bar', ['x' => 'y']);
    }
}
