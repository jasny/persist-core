<?php

declare(strict_types=1);

namespace Jasny\DB\Tests\Schema;

use Jasny\DB\Schema\Relationship;
use PHPUnit\Framework\TestCase;

/**
 * @covers \Jasny\DB\Schema\Relationship
 */
class RelationshipTest extends TestCase
{
    protected Relationship $oneToOne;
    protected Relationship $oneToMany;
    protected Relationship $manyToOne;
    protected Relationship $manyToMany;

    public function setUp(): void
    {
        $this->oneToOne = new Relationship(Relationship::ONE_TO_ONE, 'foo', 'x', 'bar', 'y');
        $this->oneToMany = new Relationship(Relationship::ONE_TO_MANY, 'foo', 'x', 'bar', 'y');
        $this->manyToOne = new Relationship(Relationship::MANY_TO_ONE, 'foo', 'x', 'bar', 'y');
        $this->manyToMany = new Relationship(Relationship::MANY_TO_MANY, 'foo', 'x', 'bar', 'y');
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
        $relationship = new Relationship(Relationship::ONE_TO_ONE, 'foo', 'x', 'bar', 'y');

        $this->assertEquals('foo', $relationship->getCollection());
        $this->assertEquals('x', $relationship->getField());
        $this->assertEquals('bar', $relationship->getRelatedCollection());
        $this->assertEquals('y', $relationship->getRelatedField());
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
        $relationship = new Relationship($type, 'foo', 'x', 'bar', 'y');
        $swapped = $relationship->swapped();

        $this->assertEquals($expectedType, $swapped->getType());

        $this->assertEquals('bar', $swapped->getCollection());
        $this->assertEquals('y', $swapped->getField());

        $this->assertEquals('foo', $swapped->getRelatedCollection());
        $this->assertEquals('x', $swapped->getRelatedField());
    }

    public function testMatches()
    {
        $this->assertTrue($this->oneToOne->matches('foo', null, null, null));
        $this->assertTrue($this->oneToOne->matches('foo', 'x', null, null));
        $this->assertTrue($this->oneToOne->matches(null, null, 'bar', null));
        $this->assertTrue($this->oneToOne->matches(null, null, 'bar', 'y'));

        $this->assertTrue($this->oneToOne->matches('foo', null, 'bar', null));
        $this->assertTrue($this->oneToOne->matches('foo', 'x', 'bar', null));
        $this->assertTrue($this->oneToOne->matches('foo', null, 'bar', 'y'));
        $this->assertTrue($this->oneToOne->matches('foo', 'x', 'bar', 'y'));

        $this->assertFalse($this->oneToOne->matches('qux', null, null, null));
        $this->assertFalse($this->oneToOne->matches('bar', null, null, null));
        $this->assertFalse($this->oneToOne->matches('foo', 'a', null, null));
        $this->assertFalse($this->oneToOne->matches(null, null, 'qux', null));
        $this->assertFalse($this->oneToOne->matches(null, null, 'foo', null));
        $this->assertFalse($this->oneToOne->matches(null, null, 'bar', 'b'));

        $this->assertFalse($this->oneToOne->matches('foo', null, 'qux', null));
        $this->assertFalse($this->oneToOne->matches('foo', 'a', 'bar', null));
        $this->assertFalse($this->oneToOne->matches('foo', null, 'bar', 'b'));
        $this->assertFalse($this->oneToOne->matches('foo', 'y', 'bar', 'x'));
    }

    public function testInvalidTypeInConstructor()
    {
        $this->expectException(\InvalidArgumentException::class);

        new Relationship(9999, 'foo', 'x', 'bar', 'y');
    }
}
