<?php

declare(strict_types=1);

namespace Jasny\DB\Tests\Query;

use Improved as i;
use Jasny\DB\Filter\FilterItem;
use Jasny\DB\Map\NoMap;
use Jasny\DB\Option\Functions as opt;
use Jasny\DB\Map\FieldMap;
use Jasny\DB\Query\ApplyMapToFilter;
use Jasny\PHPUnit\ExpectWarningTrait;
use PHPUnit\Framework\TestCase;

/**
 * @covers \Jasny\DB\Query\ApplyMapToFilter
 */
class ApplyMapToFilterTest extends TestCase
{
    use ExpectWarningTrait;

    protected const MAP = [
        'id' => '_id',
        'foo' => 'foos',
        'bar' => 'bor',
        'skippy' => false
    ];

    protected function createFilter()
    {
        return [
            new FilterItem('id', 'not', 42),
            new FilterItem('id', 'min', 1),
            new FilterItem('bar', '', 'hello'),
            new FilterItem('numbers', 'in', [1, 2, 3]),
            new FilterItem('foo.bar.qux', '', 1),
            new FilterItem('skippy', '', 100),
        ];
    }

    public function test()
    {
        $filter = $this->createFilter();

        $map = new FieldMap(self::MAP);
        $opts = [opt\setting('map', $map)];

        $applyMap = new ApplyMapToFilter();
        $iterator = $applyMap->prepare($filter, $opts);

        $this->assertIsIterable($iterator);
        $mapped = i\iterable_to_array($iterator, true);

        $this->assertCount(5, $mapped);

        $this->assertEquals(new FilterItem('_id', 'not', 42), $mapped[0]);
        $this->assertEquals(new FilterItem('_id', 'min', 1), $mapped[1]);
        $this->assertEquals(new FilterItem('bor', '', 'hello'), $mapped[2]);
        $this->assertSame($filter[3], $mapped[3]);
        $this->assertEquals(new FilterItem('foos.bar.qux', '', 1), $mapped[4]);
    }

    public function noMapProvider()
    {
        return [
            'without' => [[]],
            'NoMap'   => [[opt\setting('map', new NoMap())]],
            'invalid' => [[opt\setting('map', 'hello')]],
        ];
    }

    /**
     * @dataProvider noMapProvider
     */
    public function testNoMap(array $opts)
    {
        $filter = $this->createFilter();

        $applyMap = new ApplyMapToFilter();
        $iterator = $applyMap->prepare($filter, $opts);

        $this->assertSame($filter, $iterator);
    }
}
