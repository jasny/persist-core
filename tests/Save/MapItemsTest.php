<?php

declare(strict_types=1);

namespace Jasny\DB\Tests\Save;

use Improved as i;
use Jasny\DB\Map\NoMap;
use Jasny\DB\Save\MapItems;
use Jasny\DB\Option\Functions as opts;
use Jasny\DB\Map\FieldMap;
use PHPUnit\Framework\TestCase;

/**
 * @covers \Jasny\DB\Save\MapItems
 */
class MapItemsTest extends TestCase
{
    protected const MAP = [
        'id' => '_id',
        'foo' => 'foos',
        'bar' => 'bor',
        'skippy' => false
    ];

    public function test()
    {
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
        $opts = [opts\setting('map', $map)];

        $applyTo = new MapItems();
        $iterator = $applyTo($items, $opts);

        $this->assertIsIterable($iterator);
        $mapped = i\iterable_to_array($iterator, true);

        $this->assertCount(3, $mapped);
        $this->assertEquals($expected, $mapped);
    }


    public function noMapProvider()
    {
        return [
            [[]],
            [[opts\setting('map', new NoMap())]],
        ];
    }

    /**
     * @dataProvider noMapProvider
     */
    public function testNoMap(array $opts)
    {
        $items = [
            ['id' => 1, 'name' => 'one', 'foo' => ['x'], 'skippy' => 42],
            ['id' => 2, 'name' => 'two', 'foo' => ['y'], 'bar' => 'b'],
            ['id' => 3, 'name' => 'three', 'foo' => [], 'skippy' => 99],
        ];

        $applyTo = new MapItems();
        $mapped = $applyTo($items, $opts);

        $this->assertSame($items, $mapped);
    }
}
