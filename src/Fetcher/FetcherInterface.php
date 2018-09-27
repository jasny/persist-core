<?php

declare(strict_types=1);

namespace Jasny\DB\Fetcher;

use Jasny\IteratorPipeline\Pipeline;

/**
 * Object to fetch data from a source
 */
interface FetcherInterface
{
    /**
     * Fetch data and make it available as iterable.
     *
     * @param array $filter  Filter parameters
     * @param array $opts
     * @return iterable
     */
    public function fetchData(array $filter = [], array $opts = []);

    /**
     * Fetch the number of entities in the set.
     *
     * @param array $filter
     * @param array $opts
     * @return int
     */
    public function count(array $filter = [], array $opts = []): int;
}
