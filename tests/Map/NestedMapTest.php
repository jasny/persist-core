<?php

declare(strict_types=1);

namespace Jasny\DB\Tests\Map;

use Improved as i;
use Jasny\DB\Map\ChildMap;
use Jasny\DB\Map\FieldMap;
use Jasny\DB\Map\NestedMap;
use Jasny\DB\Filter\FilterItem;
use Jasny\DB\Update\UpdateInstruction;
use PHPUnit\Framework\TestCase;

use function Jasny\objectify;

/**
 * @covers \Jasny\DB\Map\NestedMap
 * @covers \Jasny\DB\Map\ChildMap
 * @covers \Jasny\DB\Map\Traits\CombineTrait
 */
class NestedMapTest extends TestCase
{
    protected const EXPECTED_MAPPING = [
        'one'            => 'uno',
        'one.color'      => 'uno.color',
        'one.color.red'  => 'uno.color.roja',

        'two'            => 'dos',
        'two.type'       => 'dos.type',
        'two.foo'        => 'dos.oof',
        'two.qul'        => false,

        'zero'           => 'zero',
        'zero.banana'    => 'zero.radish',
    ];

    protected NestedMap $map;

    public function setUp(): void
    {
        $this->map = (new NestedMap(['one' => 'uno', 'two' => 'dos']))
            ->withMappedField('one.color', ['red' => 'roja', 'blue' => 'azul'])
            ->withMappedField('two[]', ['foo' => 'oof', 'bar' => 'rab', 'qul' => false])
            ->withMappedField('zero', ['banana' => 'radish']);
    }

    public function testInnerMap()
    {
        $inner = $this->map->getInnerMaps();

        $this->assertCount(4, $inner);

        $this->assertArrayHasKey('', $inner);
        $this->assertEquals(new FieldMap(['one' => 'uno', 'two' => 'dos']), $inner['']);

        $this->assertArrayHasKey('one.color', $inner);
        $this->assertInstanceOf(ChildMap::class, $inner['one.color']);
        $this->assertEquals(new FieldMap(['red' => 'roja', 'blue' => 'azul']), $inner['one.color']->getInnerMap());
        $this->assertFalse($inner['one.color']->isForMany());

        $this->assertArrayHasKey('two', $inner);
        $this->assertInstanceOf(ChildMap::class, $inner['two']);
        $this->assertEquals(new FieldMap(['foo' => 'oof', 'bar' => 'rab', 'qul' => false]), $inner['two']->getInnerMap());
        $this->assertTrue($inner['two']->isForMany());

        $this->assertArrayHasKey('zero', $inner);
        $this->assertInstanceOf(ChildMap::class, $inner['zero']);
        $this->assertEquals(new FieldMap(['banana' => 'radish']), $inner['zero']->getInnerMap());
        $this->assertFalse($inner['zero']->isForMany());
    }

    public function fieldProvider()
    {
        $provider = [];

        foreach (self::EXPECTED_MAPPING as $appField => $dbField) {
            $provider[$appField] = [$appField, $dbField];
        }

        return $provider;
    }

    /**
     * @dataProvider fieldProvider
     */
    public function testToDB($field, $expected)
    {
        $this->assertSame($expected, $this->map->toDB($field));
    }

    public function testForFilter()
    {
        $filter = [];
        $expected = [];

        foreach (self::EXPECTED_MAPPING as $appField => $dbField) {
            $dbField = $dbField !== false ? $dbField : 'dos.qul';

            $filter[] = new FilterItem($appField, '', 1);
            $expected[] = new FilterItem($dbField, '', 1);
        }

        $applyTo = $this->map->forFilter();
        $result = i\iterable_to_array($applyTo($filter), true);

        $this->assertEquals($expected, $result);
    }

    public function testForUpdateWithManyInstructions()
    {
        $update = [];
        $expected = [];

        foreach (self::EXPECTED_MAPPING as $appField => $dbField) {
            $update[] = new UpdateInstruction('set', [$appField => 1]);
            if ($dbField !== false) {
                $expected[] = new UpdateInstruction('set', [$dbField => 1]);
            }
        }

        $applyTo = $this->map->forUpdate();
        $result = i\iterable_to_array($applyTo($update));

        $this->assertEquals($expected, $result);
    }

    public function testForUpdateWithSingleInstruction()
    {
        $update = [];
        $expected = [];

        foreach (self::EXPECTED_MAPPING as $appField => $dbField) {
            $update[$appField] = 1;
            if ($dbField !== false) {
                $expected[$dbField] = 1;
            }
        }

        $applyTo = $this->map->forUpdate();
        $result = i\iterable_to_array($applyTo([new UpdateInstruction('set', $update)]));

        $this->assertEquals([new UpdateInstruction('set', $expected)], $result);
    }


    public function dataProvider()
    {
        $items = [
            [
                'zero' => ['banana' => '€', 'peanuts' => '€€'],
                'one' => ['color' => ['red' => 77, 'blue' => 0], 'weight' => 101],
                'two' => [
                    ['type' => 'A', 'foo' => 'i'],
                    ['type' => 'A', 'foo' => 'o'],
                ],
                'ten' => 'birds',
            ],
            [
                'zero' => ['grape' => '€€'],
                'one' => ['color' => ['red' => 281, 'blue' => 30], 'weight' => 72],
                'two' => [
                    ['type' => 'A', 'foo' => 'r'],
                    ['type' => 'B', 'foo' => 't'],
                ],
                'ten' => 'bees',
            ],
            [
                'one' => ['color' => ['red' => 0, 'blue' => 99], 'weight' => 123],
                'two' => [],
                'ten' => 'trees',
            ],
        ];

        $results = [
            [
                'zero' => ['radish' => '€', 'peanuts' => '€€'],
                'uno' => ['color' => ['roja' => 77, 'azul' => 0], 'weight' => 101],
                'dos' => [
                    ['type' => 'A', 'oof' => 'i'],
                    ['type' => 'A', 'oof' => 'o'],
                ],
                'ten' => 'birds',
            ],
            [
                'zero' => ['grape' => '€€'],
                'uno' => ['color' => ['roja' => 281, 'azul' => 30], 'weight' => 72],
                'dos' => [
                    ['type' => 'A', 'oof' => 'r'],
                    ['type' => 'B', 'oof' => 't'],
                ],
                'ten' => 'bees',
            ],
            [
                'uno' => ['color' => ['roja' => 0, 'azul' => 99], 'weight' => 123],
                'dos' => [],
                'ten' => 'trees',
            ],
        ];

        return [
            'array'       => [$items, $results],
            'objects'     => [objectify($items), objectify($results)],
            'ArrayObject' => [$this->castToArrayObjects($items), $this->castToArrayObjects($results)]
        ];
    }

    private function castToArrayObjects(array $array)
    {
        foreach ($array as &$item) {
            if (is_array($item)) {
                $item = new \ArrayObject($this->castToArrayObjects($item));
            }
        }

        return $array;
    }

    /**
     * @dataProvider dataProvider
     */
    public function testForResult($expected, $result)
    {
        $applyTo = $this->map->forResult();
        $iterator = $applyTo($result);

        $this->assertIsIterable($iterator);
        $this->assertIsNotArray($iterator);

        $items = i\iterable_to_array($iterator, true);

        $this->assertEquals($expected, $items);
    }

    /**
     * @dataProvider dataProvider
     */
    public function testForItems($items, $expected)
    {
        $applyTo = $this->map->forItems();
        $iterator = $applyTo($items);

        $this->assertIsIterable($iterator);
        $this->assertIsNotArray($iterator);

        $result = i\iterable_to_array($iterator, true);

        $this->assertEquals($expected, $result);
    }
}
