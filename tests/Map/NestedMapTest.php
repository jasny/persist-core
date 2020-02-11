<?php

declare(strict_types=1);

namespace Jasny\DB\Tests\Map;

use Improved as i;
use Jasny\DB\Map\NestedMap;
use Jasny\DB\Filter\FilterItem;
use Jasny\DB\Update\UpdateInstruction;
use PHPUnit\Framework\TestCase;

use function Jasny\objectify;

/**
 * @covers \Jasny\DB\Map\NestedMap
 * @covers \Jasny\DB\Map\Traits\CombineTrait
 */
class NestedMapTest extends TestCase
{
    protected const EXPECTED_MAPPING = [
        'one'                 => 'uno',
        'one.shape'           => 'uno.forma',
        'one.shape.x'         => 'uno.forma.x',

        'two.info'            => 'dos',
        'two.info.items'      => 'dos.items',
        'two.info.items.type' => 'dos.items.type',
        'two.info.items.foo'  => 'dos.items.oof',

        'zero'                => 'zero',
        'zero.banana'         => 'zero.radish',
    ];

    protected NestedMap $map;

    public function setUp(): void
    {
        $this->map = (new NestedMap(['one' => 'uno', 'two/info' => 'dos']))
            ->withMappedField('one', [
                'shape' => 'forma',
                'red' => 'color/roja',
                'blue' => 'color/azul',
            ])
            ->withMappedField('two.info.items[]', ['foo' => 'oof', 'bar' => 'rab'])
            ->withMappedField('zero', ['banana' => 'radish']);
    }

    public function testForFilter()
    {
        $filter = [];
        $expected = [];

        foreach (self::EXPECTED_MAPPING as $appField => $dbField) {
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
            $expected[] = new UpdateInstruction('set', [$dbField => 1]);
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
            $expected[$dbField] = 1;
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
                'one' => ['shape' => ['x' => 10, 'y' => 7], 'red' => 77, 'blue' => 0, 'weight' => 101],
                'two' => [
                    'info' => [
                        'pop' => 4,
                        'items' => [
                            ['type' => 'A', 'foo' => 'i'],
                            ['type' => 'A', 'foo' => 'o'],
                        ]
                    ],
                    'meta' => ['a' => 'b'],
                ],
                'ten' => 'birds',
            ],
            [
                'zero' => ['grape' => '€€'],
                'one' => ['shape' => ['x' => 5, 'y' => 23], 'red' => 281, 'blue' => 30, 'weight' => 72],
                'two' => [
                    'info' => [
                        'items' => [
                            ['type' => 'A', 'foo' => 'r'],
                            ['type' => 'B', 'foo' => 't'],
                        ]
                    ],
                    'meta' => [],
                ],
                'ten' => 'bees',
            ],
            [
                'zero' => ['banana' => '€€', 'grape' => '€€€€'],
                'one' => ['shape' => ['x' => 51, 'y' => 21], 'red' => 0, 'blue' => 99, 'weight' => 123],
                'two' => [
                    'info' => [
                        'items' => []
                    ],
                    'meta' => [],
                ],
                'ten' => 'trees',
            ],
        ];

        $results = [
            [
                'zero' => ['radish' => '€', 'peanuts' => '€€'],
                'uno' => ['forma' => ['x' => 10, 'y' => 7], 'color' => ['roja' => 77, 'azul' => 0], 'weight' => 101],
                'dos' => [
                    ['type' => 'A', 'oof' => 'i'],
                    ['type' => 'A', 'oof' => 'o'],
                ],
                'ten' => 'birds',
            ],
            [
                'zero' => ['grape' => '€€'],
                'one' => ['forma' => ['x' => 5, 'y' => 23], 'color' => ['roja' => 281, 'azul' => 30], 'weight' => 72],
                'dos' => [
                    ['type' => 'A', 'oof' => 'r'],
                    ['type' => 'B', 'oof' => 't'],
                ],
                'ten' => 'bees',
            ],
            [
                'zero' => ['radish' => '€€', 'grape' => '€€€€'],
                'one' => ['forma' => ['x' => 51, 'y' => 21], 'color' => ['roja' => 0, 'azul' => 99], 'weight' => 123],
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
        foreach ($expected as &$item) {
            if (isset($item['two']['info']['pop'])) {
                unset($item['two']['info']['pop']);
            }
            unset($item['two']['meta']);
        }

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
