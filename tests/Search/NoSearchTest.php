<?php

namespace Jasny\DB\Tests\Search;

use Jasny\DB\Search\NoSearch;
use PHPUnit\Framework\TestCase;

/**
 * @covers \Jasny\DB\Search\NoSearch
 */
class NoSearchTest extends TestCase
{
    /**
     * @expectedException \Jasny\DB\Exception\UnsupportedFeatureException
     */
    public function testSearch()
    {
        $search = new NoSearch();

        $search->search([], 'foo');
    }
}
