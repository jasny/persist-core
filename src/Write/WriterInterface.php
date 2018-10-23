<?php

declare(strict_types=1);

namespace Jasny\DB\Write;

use Jasny\DB\QueryBuilder\QueryBuilderInterface;
use Jasny\DB\Read\Result;
use Jasny\DB\Update\UpdateOperation;

/**
 * Service to add, update, and delete data from a persistent data storage (DB table, collection, etc).
 */
interface WriterInterface
{
    /**
     * Create a Writer service with a custom filter query builder.
     *
     * @param QueryBuilderInterface $builder
     * @return static
     */
    public function withQueryBuilder(QueryBuilderInterface $builder);

    /**
     * Create a Writer service with a custom builder pipeline for save.
     *
     * @param QueryBuilderInterface $builder
     * @return static
     */
    public function withSaveQueryBuilder(QueryBuilderInterface $builder);

    /**
     * Create a Writer service with a custom update query builder.
     *
     * @param QueryBuilderInterface $builder
     * @return static
     */
    public function withUpdateQueryBuilder(QueryBuilderInterface $builder);


    /**
     * Save the data.
     * Returns an array with generated properties per entry.
     *
     * @param mixed    $storage
     * @param iterable $items
     * @param array    $opts
     * @return Result
     */
    public function save($storage, iterable $items, array $opts = []): Result;

    /**
     * Query and update records.
     *
     * @param mixed                             $storage
     * @param array                             $filter
     * @param UpdateOperation|UpdateOperation[] $changes
     * @param array                             $opts
     * @return Result
     */
    public function update($storage, array $filter, $changes, array $opts = []): Result;

    /**
     * Query and delete records.
     *
     * @param mixed $storage
     * @param array $filter
     * @param array $opts
     * @return Result
     */
    public function delete($storage, array $filter, array $opts = []): Result;
}
