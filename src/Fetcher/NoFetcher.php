<?php

declare(strict_types=1);

namespace Jasny\DB\Fetcher;

use Jasny\DB\Exception\UnsupportedFeatureException;

/**
 * Fetching data / multiple records is not supported.
 */
class NoFetcher implements FetcherInterface
{
    /**
     * Fetch data and make it available as iterable.
     *
     * @param array $filter Filter parameters
     * @param array $opts
     * @return iterable
     * @throws UnsupportedFeatureException
     */
    public function fetchData(array $filter = [], array $opts = []): iterable
    {
        throw new UnsupportedFeatureException("Fetching data is not supported.");
    }

    /**
     * Fetch the number of entities in the set.
     *
     * @param array $filter
     * @param array $opts
     * @return int
     */
    public function count(array $filter = [], array $opts = []): int
    {
        throw new UnsupportedFeatureException("Fetching data is not supported.");
    }
}
