<?php

declare(strict_types=1);

namespace Jasny\DB;

use Jasny\EntityCollectionInterface;

interface FetchInterface
{
    /**
     * Fetch all entities from the set.
     *
     * @param array $filter
     * @param array $opts
     * @return EntityCollectionInterface|EntityInterface\[]
     */
    public function fetchAll(array $filter = [], array $opts = []): EntityCollectionInterface;

    /**
     * Fetch data and make it available through an iterator
     *
     * @param array $filter
     * @param array $opts
     * @return iterable
     */
    public function fetchList(array $filter = [], array $opts = []): iterable;

    /**
     * Fetch id/description pairs.
     *
     * @param array $filter
     * @param array $opts
     * @return array
     */
    public function fetchPairs(array $filter = [], array $opts = []): array;

    /**
     * Fetch the number of entities in the set.
     *
     * @param array $filter
     * @param array $opts
     * @return int
     */
    public function count(array $filter = [], array $opts = []): int;

}