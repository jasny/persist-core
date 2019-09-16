<?php

declare(strict_types=1);

namespace Jasny\DB\Write;

use Improved\IteratorPipeline\PipelineBuilder;
use Jasny\DB\Option\OptionInterface;
use Jasny\DB\Update\UpdateOperation;
use Jasny\DB\QueryBuilder\QueryBuilderInterface;
use Jasny\DB\Result;

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
     * Create a Writer service with a custom filter query builder.
     */
    public function withQueryBuilder(QueryBuilderInterface $builder): self;

    /**
     * Create a Writer service with a custom builder pipeline for save.
     */
    public function withSaveQueryBuilder(QueryBuilderInterface $builder): self;

    /**
     * Create a Writer service with a custom update query builder.
     */
    public function withUpdateQueryBuilder(QueryBuilderInterface $builder): self;

    /**
     * Create a Writer service with a custom update query builder.
     */
    public function withResultBuilder(PipelineBuilder $builder): self;


    /**
     * Save the data.
     * Result contains generated properties for each item item.
     */
    public function save(iterable $items, array $opts = []): Result;

    /**
     * Query and update records.
     *
     * @param array             $filter
     * @param UpdateOperation[] $changes
     * @param OptionInterface[] $opts
     * @return Result
     */
    public function update(array $filter, array $changes, array $opts = []): Result;

    /**
     * Query and delete records.
     *
     * @param array             $filter
     * @param OptionInterface[] $opts
     * @return Result
     */
    public function delete(array $filter, array $opts = []): Result;
}
