<?php

namespace Jasny\DB\Tests\Search;

use Jasny\DB\QueryBuilder\QueryBuilderInterface;
use Jasny\DB\Read\NoWrite;
use PHPUnit\Framework\TestCase;

/**
 * @covers \Jasny\DB\Read\NoWrite
 */
class NoSearchTest extends TestCase
{
    public function testWithQueryBuilder()
    {
        $builder = $this->createMock(QueryBuilderInterface::class);

        $base = new NoWrite();
        $search = $base->withQueryBuilder($builder);

        $this->assertSame($base, $search);
    }

    /**
     * @expectedException \Jasny\DB\Exception\UnsupportedFeatureException
     */
    public function testSearch()
    {
        $search = new NoWrite();

        $search->search([], 'foo');
    }
}
