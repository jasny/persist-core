<?php

declare(strict_types=1);

namespace Jasny\DB\CRUD;

use Jasny\DB\QueryBuilder\QueryBuilderInterface;
use Jasny\DB\Result\Result;

/**
 * Service to fetch, save and delete data from a persistent data storage (DB table, collection, etc).
 */
interface CRUDInterface
{
    /**
     * Create a CRUD service with a custom query builder.
     *
     * @param QueryBuilderInterface $queryBuilder
     * @return mixed
     */
    public function withQueryBuilder(QueryBuilderInterface $queryBuilder);


    /**
     * Query and fetch data.
     *
     * @param mixed $storage
     * @param array $filter
     * @param array $opts
     * @return Result
     */
    public function fetch($storage, array $filter = null, array $opts = []): Result;

    /**
     * Query and count result.
     *
     * @param mixed $storage
     * @param array $filter
     * @param array $opts
     * @return int
     */
    public function count($storage, array $filter = null, array $opts = []): int;


    /**
     * Query and update records.
     *
     * @param mixed $storage
     * @param array $filter
     * @param array $changes
     * @param array $opts
     * @return void
     */
    public function update($storage, array $filter, array $changes, array $opts = []): void;

    /**
     * Save the data.
     * Returns an array with generated properties per entry.
     *
     * @param mixed    $storage
     * @param iterable $items
     * @param array    $opts
     * @return iterable
     */
    public function save($storage, iterable $items, array $opts = []): iterable;

    /**
     * Delete data.
     * Returns an array with generated properties per entry.
     *
     * @param mixed $storage
     * @param array $filter
     * @param array $opts
     * @return void
     */
    public function delete($storage, array $filter = null, array $opts = []): void;
}
