<?php

declare(strict_types=1);

namespace Jasny\DB\Write;

use Jasny\DB\QueryBuilder\QueryBuilderInterface;

/**
 * Service to fetch, save and delete data from a persistent data storage (DB table, collection, etc).
 */
interface WriterInterface
{
    /**
     * Create a CRUD service with a custom query builder.
     *
     * @param QueryBuilderInterface $queryBuilder
     * @return mixed
     */
    public function withQueryBuilder(QueryBuilderInterface $queryBuilder);


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
     * Query and update records.
     *
     * @param mixed     $storage
     * @param \stdClass $changes
     * @param array     $filter
     * @param array     $opts
     * @return void
     */
    public function update($storage, \stdClass $changes, array $filter, array $opts = []): void;

    /**
     * Query and delete records.
     *
     * @param mixed $storage
     * @param array $filter
     * @param array $opts
     * @return void
     */
    public function delete($storage, array $filter = null, array $opts = []): void;
}
