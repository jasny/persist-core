<?php

declare(strict_types=1);

namespace Jasny\Tests\Persist\Query;

use Improved as i;
use Jasny\Persist\Map\NoMap;
use Jasny\Persist\Query\ApplyMapToItems;
use Jasny\Persist\Option\Functions as opt;
use Jasny\Persist\Map\FieldMap;
use PHPUnit\Framework\TestCase;

/**
 * @covers \Jasny\Persist\Query\ApplyMapToItems
 */
class ApplyMapToItemsTest extends TestCase
{
    protected const MAP = [
        'id' => '_id',
        'foo' => 'foos',
        'bar' => 'bor',
        'skippy' => false
    ];

    public function test()
    {
        $acc = (object)[];
        $items = [
            ['id' => 1, 'name' => 'one', 'foo' => ['x'], 'skippy' => 42],
            ['id' => 2, 'name' => 'two', 'foo' => ['y'], 'bar' => 'b'],
            ['id' => 3, 'name' => 'three', 'foo' => [], 'skippy' => 99],
        ];

        $expected = [
            ['_id' => 1, 'name' => 'one', 'foos' => ['x']],
            ['_id' => 2, 'name' => 'two', 'foos' => ['y'], 'bor' => 'b'],
            ['_id' => 3, 'name' => 'three', 'foos' => []],
        ];

        $map = new FieldMap(self::MAP);
        $opts = [opt\setting('map', $map)];

        $applyMap = new ApplyMapToItems();
        $iterator = $applyMap->compose($acc, $items, $opts);

        $this->assertIsIterable($iterator);
        $mapped = i\iterable_to_array($iterator, true);

        $this->assertCount(3, $mapped);
        $this->assertEquals($expected, $mapped);
    }


    public function noMapProvider()
    {
        return [
            [[]],
            [[opt\setting('map', new NoMap())]],
        ];
    }

    /**
     * @dataProvider noMapProvider
     */
    public function testNoMap(array $opts)
    {
        $acc = (object)[];
        $items = [
            ['id' => 1, 'name' => 'one', 'foo' => ['x'], 'skippy' => 42],
            ['id' => 2, 'name' => 'two', 'foo' => ['y'], 'bar' => 'b'],
            ['id' => 3, 'name' => 'three', 'foo' => [], 'skippy' => 99],
        ];

        $applyMap = new ApplyMapToItems();
        $mapped = $applyMap->compose($acc, $items, $opts);

        $this->assertSame($items, $mapped);
    }
}
