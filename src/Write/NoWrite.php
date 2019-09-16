<?php

declare(strict_types=1);

namespace Jasny\DB\Write;

use Improved\IteratorPipeline\PipelineBuilder;
use Jasny\DB\Option\OptionInterface;
use Jasny\DB\QueryBuilder\QueryBuilderInterface;
use Jasny\DB\Exception\UnsupportedFeatureException;
use Jasny\DB\Result;
use Jasny\DB\Update\UpdateOperation;

/**
 * Writing to storage is not supported.
 * @immutable
 */
class NoWrite implements WriteInterface
{
    /**
     * Get underlying storage object.
     *
     * @return null
     */
    public function getStorage()
    {
        return null;
    }

    /**
     * Create a Writer service with a custom filter query builder.
     *
     * @param QueryBuilderInterface $builder
     * @return $this
     */
    public function withQueryBuilder(QueryBuilderInterface $builder): WriteInterface
    {
        return $this;
    }

    /**
     * Create a Writer service with a custom builder pipeline for save.
     *
     * @param QueryBuilderInterface $builder
     * @return $this
     */
    public function withSaveQueryBuilder(QueryBuilderInterface $builder): WriteInterface
    {
        return $this;
    }

    /**
     * Create a Writer service with a custom update query builder.
     *
     * @param QueryBuilderInterface $builder
     * @return $this
     */
    public function withUpdateQueryBuilder(QueryBuilderInterface $builder): WriteInterface
    {
        return $this;
    }

    /**
     * Create a Writer service with a custom update query builder.
     *
     * @param PipelineBuilder $builder
     * @return $this
     */
    public function withResultBuilder(PipelineBuilder $builder): WriteInterface
    {
        return $this;
    }


    /**
     * Update is not supported
     *
     * @param array             $filter
     * @param UpdateOperation[] $changes
     * @param OptionInterface[] $opts
     * @return Result
     * @throws UnsupportedFeatureException
     */
    public function update(array $filter, array $changes, array $opts = []): Result
    {
        throw new UnsupportedFeatureException("Writing to storage is not supported");
    }

    /**
     * Save is not supported
     *
     * @param iterable $items
     * @param array    $opts
     * @return Result
     * @throws UnsupportedFeatureException
     */
    public function save(iterable $items, array $opts = []): Result
    {
        throw new UnsupportedFeatureException("Writing to storage is not supported");
    }

    /**
     * Delete is not supported
     *
     * @param array $filter
     * @param array $opts
     * @return Result
     * @throws UnsupportedFeatureException
     */
    public function delete(array $filter, array $opts = []): Result
    {
        throw new UnsupportedFeatureException("Writing to storage is not supported");
    }
}
