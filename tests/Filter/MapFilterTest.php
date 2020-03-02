<?php

declare(strict_types=1);

namespace Jasny\DB\Tests\Filter;

use Improved as i;
use Jasny\DB\Filter\FilterItem;
use Jasny\DB\Filter\MapFilter;
use Jasny\DB\Option\Functions as opts;
use Jasny\DB\Map\FieldMap;
use PHPUnit\Framework\TestCase;

/**
 * @covers \Jasny\DB\Filter\MapFilter
 */
class MapFilterTest extends TestCase
{
    protected const MAP = [
        'id' => '_id',
        'foo' => 'foos',
        'bar' => 'bor',
        'skippy' => false
    ];

    public function test()
    {
        $filter = [
            new FilterItem('id', 'not', 42),
            new FilterItem('id', 'min', 1),
            new FilterItem('bar', '', 'hello'),
            new FilterItem('numbers', 'in', [1, 2, 3]),
            new FilterItem('foo.bar.qux', '', 1),
            new FilterItem('skippy', '', 100),
        ];

        $map = new FieldMap(self::MAP);
        $opts = [opts\setting('map', $map)];

        $applyTo = new MapFilter();
        $iterator = $applyTo($filter, $opts);

        $this->assertIsIterable($iterator);
        $mapped = i\iterable_to_array($iterator, true);

        $this->assertCount(6, $mapped);

        $this->assertEquals(new FilterItem('_id', 'not', 42), $mapped[0]);
        $this->assertEquals(new FilterItem('_id', 'min', 1), $mapped[1]);
        $this->assertEquals(new FilterItem('bor', '', 'hello'), $mapped[2]);
        $this->assertSame($filter[3], $mapped[3]);
        $this->assertEquals(new FilterItem('foos.bar.qux', '', 1), $mapped[4]);
        $this->assertSame($filter[5], $mapped[5]);
    }
}
