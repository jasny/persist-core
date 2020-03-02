<?php

declare(strict_types=1);

namespace Jasny\DB\Tests\Map;

use Improved\IteratorPipeline\Pipeline;
use Jasny\DB\Map\ChildMap;
use Jasny\DB\Map\FieldMap;
use Jasny\DB\Map\NestedMap;
use PHPUnit\Framework\TestCase;

use function Jasny\objectify;

/**
 * @covers \Jasny\DB\Map\NestedMap
 * @covers \Jasny\DB\Map\Traits\CombineTrait
 */
class NestedMapTest extends TestCase
{
    protected NestedMap $map;

    public function setUp(): void
    {
        $this->map = (new NestedMap(['one' => 'uno', 'two' => 'dos']))
            ->withMappedField('one.color', ['red' => 'rojo', 'blue' => 'azul'])
            ->withMappedField('two[]', ['foo' => 'oof', 'bar' => 'rab', 'qul' => false])
            ->withMappedField('zero', ['banana' => 'radish']);
    }

    public function testWithOpts()
    {
        $this->assertSame($this->map, $this->map->withOpts([]));
    }

    public function tetGetInner()
    {
        $inner = $this->map->getInner();

        $this->assertCount(4, $inner);

        $this->assertArrayHasKey('', $inner);
        $this->assertEquals(new FieldMap(['one' => 'uno', 'two' => 'dos']), $inner['']);

        $this->assertArrayHasKey('one.color', $inner);
        $this->assertInstanceOf(ChildMap::class, $inner['one.color']);
        $this->assertEquals(new FieldMap(['red' => 'rojo', 'blue' => 'azul']), $inner['one.color']->getInnerMap());
        $this->assertFalse($inner['one.color']->isForMany());

        $this->assertArrayHasKey('two', $inner);
        $this->assertInstanceOf(ChildMap::class, $inner['two']);
        $this->assertEquals(new FieldMap(['foo' => 'oof', 'bar' => 'rab', 'qul' => false]), $inner['two']->getInner());
        $this->assertTrue($inner['two']->isForMany());

        $this->assertArrayHasKey('zero', $inner);
        $this->assertInstanceOf(ChildMap::class, $inner['zero']);
        $this->assertEquals(new FieldMap(['banana' => 'radish']), $inner['zero']->getInnerMap());
        $this->assertFalse($inner['zero']->isForMany());
    }

    public function fieldProvider()
    {
        $expected = [
            'one'            => 'uno',
            'one.color'      => 'uno.color',
            'one.color.red'  => 'uno.color.rojo',

            'two'            => 'dos',
            'two.type'       => 'dos.type',
            'two.foo'        => 'dos.oof',
            'two.qul'        => false,

            'zero'           => null,
            'zero.banana'    => 'zero.radish',
        ];

        $provider = [];

        foreach ($expected as $appField => $dbField) {
            $provider[$appField] = [$appField, $dbField];
        }

        return $provider;
    }

    /**
     * @dataProvider fieldProvider
     */
    public function testApplyToField($field, $expected)
    {
        $this->assertSame($expected, $this->map->applyToField($field));
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
                'uno' => ['color' => ['rojo' => 77, 'azul' => 0], 'weight' => 101],
                'dos' => [
                    ['type' => 'A', 'oof' => 'i'],
                    ['type' => 'A', 'oof' => 'o'],
                ],
                'ten' => 'birds',
            ],
            [
                'zero' => ['grape' => '€€'],
                'uno' => ['color' => ['rojo' => 281, 'azul' => 30], 'weight' => 72],
                'dos' => [
                    ['type' => 'A', 'oof' => 'r'],
                    ['type' => 'B', 'oof' => 't'],
                ],
                'ten' => 'bees',
            ],
            [
                'uno' => ['color' => ['rojo' => 0, 'azul' => 99], 'weight' => 123],
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
    public function testApply($items, $expected)
    {
        $result = Pipeline::with($items)
            ->map(fn($item) => $this->map->apply($item))
            ->toArray();

        $this->assertEquals($expected, $result);
    }

    /**
     * @dataProvider dataProvider
     */
    public function testApplyInverse($expected, $result)
    {
        $items = Pipeline::with($result)
            ->map(fn($item) => $this->map->applyInverse($item))
            ->toArray();

        $this->assertEquals($expected, $items);
    }
}
