<?php

declare(strict_types=1);

namespace Jasny\DB\Tests\FieldMap;

use Improved as i;
use Improved\IteratorPipeline\Pipeline;
use Jasny\DB\FieldMap\ConfiguredFieldMap;
use PHPUnit\Framework\TestCase;

/**
 * @covers \Jasny\DB\FieldMap\ConfiguredFieldMap
 */
class ConfiguredFieldMapTest extends TestCase
{
    /**
     * @var ConfiguredFieldMap
     */
    protected $fieldMap;

    public function setUp(): void
    {
        $this->fieldMap = new ConfiguredFieldMap(['id' => '_id', 'foo' => 'foos', 'bar' => 'bor']);
    }


    public function testToArray()
    {
        $this->assertEquals(['id' => '_id', 'foo' => 'foos', 'bar' => 'bor'], $this->fieldMap->toArray());
    }

    public function testFlip()
    {
        $flipped = $this->fieldMap->flip();

        $this->assertInstanceOf(ConfiguredFieldMap::class, $flipped);
        $this->assertEquals(['_id' => 'id', 'foos' => 'foo', 'bor' => 'bar'], $flipped->toArray());
        $this->assertTrue($flipped->isDynamic());
    }

    public function testFlipStatic()
    {
        $this->fieldMap = new ConfiguredFieldMap(['foo' => 'bar'], false);
        $flipped = $this->fieldMap->flip();

        $this->assertInstanceOf(ConfiguredFieldMap::class, $flipped);
        $this->assertEquals(['bar' => 'foo'], $flipped->toArray());
        $this->assertFalse($flipped->isDynamic());
    }

    public function subjectProvider()
    {
        $subject = [
            'id' => 42,
            'bar' => 'man',
            'color' => 'red'
        ];

        return [
            'array' => [$subject, null, fn(array $arr) => $arr],
            'iterator' => [
                new \ArrayIterator($subject),
                \Traversable::class,
                fn(iterable $it) => i\iterable_to_array($it, true)
            ],
            'ArrayObject' => [
                new \ArrayObject($subject),
                \ArrayObject::class,
                fn(\ArrayObject $ao) => $ao->getArrayCopy()
            ],
            'object' => [(object)$subject, \stdClass::class, fn(\stdClass $obj) => (array)$obj],
        ];
    }

    /**
     * @dataProvider subjectProvider
     */
    public function testInvokeDynamic($subject, ?string $class, callable $convert)
    {
        $mapped = ($this->fieldMap)($subject);

        $expected = [
            '_id' => 42,
            'bor' => 'man',
            'color' => 'red'
        ];

        if ($class !== null) {
            $this->assertInstanceOf($class, $mapped);
        }
        $this->assertEquals($expected, $convert($mapped));
    }

    /**
     * @dataProvider subjectProvider
     */
    public function testInvokeStatic($subject, ?string $class, callable $convert)
    {
        $this->fieldMap = new ConfiguredFieldMap(['id' => '_id', 'foo' => 'foos', 'bar' => 'bor'], false);

        $mapped = ($this->fieldMap)($subject);

        $expected = [
            '_id' => 42,
            'bor' => 'man'
        ];

        if ($class !== null) {
            $this->assertInstanceOf($class, $mapped);
        }
        $this->assertEquals($expected, $convert($mapped));
    }

    public function testInvokeInfo()
    {
        $inputFlipped = [
            42 => ['field' => 'id', 'operator' => ''],
            'man' => ['field' => 'bar', 'operator' => 'not'],
            'red' => ['field' => 'color', 'operator' => ''],
        ];

        $fields = Pipeline::with($inputFlipped)->flip();

        $mapped = ($this->fieldMap)($fields);
        ['keys' => $keys, 'values' => $values] = i\iterable_separate($mapped);

        $expectedKeys = [
            ['field' => '_id', 'operator' => ''],
            ['field' => 'bor', 'operator' => 'not'],
            ['field' => 'color', 'operator' => '']
        ];
        $expectedValues = [42, 'man', 'red'];

        $this->assertEquals($expectedKeys, $keys);
        $this->assertEquals($expectedValues, $values);
    }

    public function testInvokeNoneIterable()
    {
        $this->assertEquals(100, ($this->fieldMap)(100));
        $this->assertEquals('hello', ($this->fieldMap)('hello'));
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

    public function testOffsetSet()
    {
        $this->expectException(\LogicException::class);
        $this->fieldMap['zoo'] = 'ape';
    }

    public function testOffsetUnset()
    {
        $this->expectException(\LogicException::class);
        unset($this->fieldMap['bar']);
    }
}
