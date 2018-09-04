<?php

declare(strict_types=1);

namespace Jasny\DB\Fetch;

use Jasny\DB\EntityInterface;
use Jasny\DB\FetchInterface;
use Jasny\EntityCollectionInterface;

/**
 * Fetching data is not supported
 */
class NoFetch implements FetchInterface
{

    /**
     * Fetch all entities from the set.
     *
     * @param array $filter
     * @param array $opts
     * @return EntityCollectionInterface|EntityInterface\[]
     */
    public function fetchAll(array $filter = [], array $opts = []): EntityCollectionInterface
    {
        throw new \BadMethodCallException("Fetching data is not supported");
    }

    /**
     * Fetch data and make it available through an iterator
     *
     * @param array $filter
     * @param array $opts
     * @return iterable
     */
    public function fetchList(array $filter = [], array $opts = []): iterable
    {
        throw new \BadMethodCallException("Fetching data is not supported");
    }

    /**
     * Fetch id/description pairs.
     *
     * @param array $filter
     * @param array $opts
     * @return array
     */
    public function fetchPairs(array $filter = [], array $opts = []): array
    {
        throw new \BadMethodCallException("Fetching data is not supported");
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
        throw new \BadMethodCallException("Fetching data is not supported");
    }
}