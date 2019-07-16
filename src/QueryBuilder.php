<?php

declare(strict_types=1);

namespace Jasny\DB;

/**
 * Interface for service that can convert a filter to a database specific query.
 */
interface QueryBuilder
{
    /**
     * Create the query from a filter
     *
     * @param iterable $filter
     * @param array    $opts
     * @return mixed
     */
    public function buildQuery(iterable $filter, array $opts = []);

    /**
     * Alias of `buildQuery()`
     *
     * @param iterable $filter
     * @param array    $opts
     * @return mixed
     */
    public function __invoke(iterable $filter, array $opts = []);
}
