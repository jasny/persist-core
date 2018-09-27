<?php

declare(strict_types=1);

namespace Jasny\DB\Gateway;

use Jasny\DB\CRUD\CRUDInterface;
use Jasny\DB\Fetcher\FetcherInterface;
use Jasny\DB\Search\SearchInterface;
use Jasny\IteratorPipeline\Pipeline;

/**
 * Gateway to a data set, like a DB table (RDBMS) or collection (NoSQL).
 */
interface GatewayInterface extends CRUDInterface, FetcherInterface, SearchInterface
{
    /**
     * Fetch data and make it available as iterable.
     *
     * @param array $filter  Filter parameters
     * @param array $opts
     * @return Pipeline
     */
    public function fetchData(array $filter = [], array $opts = []): Pipeline;
}
