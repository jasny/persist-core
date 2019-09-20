<?php

declare(strict_types=1);

namespace Jasny\DB\Write;

use Jasny\DB\Option\OptionInterface;
use Jasny\DB\Result\ResultBuilder;
use Jasny\DB\Update\UpdateOperation;
use Jasny\DB\QueryBuilder\QueryBuilderInterface;
use Jasny\DB\Result\Result;
use Psr\Log\LoggerInterface;

/**
 * Service to add, update, and delete data from a persistent data storage (DB table, collection, etc).
 */
interface WriteInterface
{
    /**
     * Get underlying storage object.
     * This is DB implementation dependent.
     *
     * @return mixed
     */
    public function getStorage();

    /**
     * Enable (debug) logging.
     *
     * @return static
     */
    public function withLogging(LoggerInterface $logger): self;


    /**
     * Create a Writer service with a custom filter query builder.
     *
     * @param QueryBuilderInterface $builder
     * @return static
     */
    public function withQueryBuilder(QueryBuilderInterface $builder): self;

    /**
     * Create a Writer service with a custom builder pipeline for save.
     *
     * @param QueryBuilderInterface $builder
     * @return static
     */
    public function withSaveQueryBuilder(QueryBuilderInterface $builder): self;

    /**
     * Create a Writer service with a custom update query builder.
     *
     * @param QueryBuilderInterface $builder
     * @return static
     */
    public function withUpdateQueryBuilder(QueryBuilderInterface $builder): self;

    /**
     * Create a Writer service with a custom update query builder.
     *
     * @param ResultBuilder $builder
     * @return static
     */
    public function withResultBuilder(ResultBuilder $builder): self;


    /**
     * Save the one item.
     * Result contains generated properties for the item.
     *
     * @param object|array      $item
     * @param OptionInterface[] $opts
     * @return Result
     */
    public function save($item, array $opts = []): Result;

    /**
     * Save the multiple items.
     * Result contains generated properties for each item.
     *
     * @param iterable<object|array> $items
     * @param OptionInterface[]      $opts
     * @return Result
     */
    public function saveAll(iterable $items, array $opts = []): Result;

    /**
     * Query and update records.
     *
     * @param array<string, mixed> $filter
     * @param UpdateOperation[]    $changes
     * @param OptionInterface[]    $opts
     * @return Result
     */
    public function update(array $filter, array $changes, array $opts = []): Result;

    /**
     * Query and delete records.
     *
     * @param array<string, mixed> $filter
     * @param OptionInterface[]    $opts
     * @return Result
     */
    public function delete(array $filter, array $opts = []): Result;
}
