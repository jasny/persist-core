<?php

namespace Jasny\DB\Tests\Search;

use Jasny\DB\QueryBuilder\QueryBuilderInterface;
use Jasny\DB\Search\NoSearch;
use PHPUnit\Framework\TestCase;

/**
 * @covers \Jasny\DB\Search\NoSearch
 */
class NoSearchTest extends TestCase
{
    public function testWithQueryBuilder()
    {
        $builder = $this->createMock(QueryBuilderInterface::class);

        $base = new NoSearch();
        $search = $base->withQueryBuilder($builder);

        $this->assertSame($base, $search);
    }

    /**
     * @expectedException \Jasny\DB\Exception\UnsupportedFeatureException
     */
    public function testSearch()
    {
        $search = new NoSearch();

        $search->search([], 'foo');
    }
}
