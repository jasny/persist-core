<?php

declare(strict_types=1);

namespace Jasny\DB\Write;

use Improved\IteratorPipeline\PipelineBuilder;
use Jasny\DB\Option\OptionInterface;
use Jasny\DB\Update\UpdateOperation;
use Jasny\DB\QueryBuilder\QueryBuilderInterface;
use Jasny\DB\Result;
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
     * Set logger to enable logging.
     */
    public function withLogging(LoggerInterface $logger);

    
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
     * Create a Writer service with a custom update query builder.
     *
     * @param PipelineBuilder $builder
     * @return static
     */
    public function withResultBuilder(PipelineBuilder $builder);


    /**
     * Save the data.
     * Result contains generated properties for each item item.
     */
    public function save(iterable $items, array $opts = []): Result;

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
