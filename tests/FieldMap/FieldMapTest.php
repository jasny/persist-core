<?php declare(strict_types=1);

namespace Jasny\DB\Tests\FieldMap;

use Improved as i;
use Jasny\DB\FieldMap\FieldMap;
use PHPUnit\Framework\TestCase;

/**
 * @covers \Jasny\DB\FieldMap\FieldMap
 */
class FieldMapTest extends TestCase
{
    /**
     * @var FieldMap
     */
    protected $fieldMap;

    public function setUp()
    {
        $this->fieldMap = new FieldMap(['id' => '_id', 'foo' => 'foos', 'bar' => 'bor']);
    }


    public function testToArray()
    {
        $this->assertEquals(['id' => '_id', 'foo' => 'foos', 'bar' => 'bor'], $this->fieldMap->toArray());
    }

    public function testFlip()
    {
        $flipped = $this->fieldMap->flip();

        $this->assertInstanceOf(FieldMap::class, $flipped);
        $this->assertEquals(['_id' => 'id', 'foos' => 'foo', 'bor' => 'bar'], $flipped->toArray());
        $this->assertTrue($flipped->isDynamic());
    }

    public function testFlipStatic()
    {
        $this->fieldMap = new FieldMap(['foo' => 'bar'], false);
        $flipped = $this->fieldMap->flip();

        $this->assertInstanceOf(FieldMap::class, $flipped);
        $this->assertEquals(['bar' => 'foo'], $flipped->toArray());
        $this->assertFalse($flipped->isDynamic());
    }


    public function testInvoke()
    {
        $mapped = i\function_call($this->fieldMap, [
            'id' => 42,
            'bar' => 'man',
            'color' => 'red'
        ]);

        $expected = [
            '_id' => 42,
            'bor' => 'man',
            'color' => 'red'
        ];

        $this->assertEquals($expected, $mapped);
    }

    public function testInvokeStatic()
    {
        $this->fieldMap = new FieldMap(['id' => '_id', 'foo' => 'foos', 'bar' => 'bor'], false);

        $mapped = i\function_call($this->fieldMap, [
            'id' => 42,
            'bar' => 'man',
            'color' => 'red'
        ]);

        $expected = [
            '_id' => 42,
            'bor' => 'man'
        ];

        $this->assertEquals($expected, $mapped);
    }

    public function testInvokeIterator()
    {
        $values = new \ArrayIterator([
            'id' => 42,
            'bar' => 'man',
            'color' => 'red'
        ]);

        $mapped = i\function_call($this->fieldMap, $values);

        $expected = [
            '_id' => 42,
            'bor' => 'man',
            'color' => 'red'
        ];

        $this->assertInstanceOf(\Iterator::class, $mapped);
        $this->assertEquals($expected, i\iterable_to_array($mapped, true));
    }


    public function testArrayAccess()
    {
        $this->assertTrue(isset($this->fieldMap['id']));
        $this->assertEquals('_id', $this->fieldMap['id']);

        $this->assertTrue(isset($this->fieldMap['bar']));
        $this->assertEquals('bor', $this->fieldMap['bar']);

        $this->assertFalse(isset($this->fieldMap['color']));
        $this->assertNull($this->fieldMap['color']);
    }

    /**
     * @expectedException \BadMethodCallException
     */
    public function testOffsetSet()
    {
        $this->fieldMap['zoo'] = 'ape';
    }

    /**
     * @expectedException \BadMethodCallException
     */
    public function testOffsetUnset()
    {
        unset($this->fieldMap['bar']);
    }
}
