<?php

declare(strict_types=1);

namespace Jasny\DB\Write;

use Jasny\DB\Option\OptionInterface;
use Jasny\DB\QueryBuilder\QueryBuilderInterface;
use Jasny\DB\Exception\UnsupportedFeatureException;
use Jasny\DB\Result\Result;
use Jasny\DB\Result\ResultBuilder;
use Jasny\DB\Update\UpdateOperation;
use Psr\Log\LoggerInterface;

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
     * Does nothing.
     *
     * @param LoggerInterface $logger
     * @return $this
     */
    public function withLogging(LoggerInterface $logger): self
    {
        return $this;
    }

    /**
     * Create a Writer service with a custom filter query builder.
     *
     * @param QueryBuilderInterface $builder
     * @return $this
     */
    public function withQueryBuilder(QueryBuilderInterface $builder): self
    {
        return $this;
    }

    /**
     * Create a Writer service with a custom builder pipeline for save.
     *
     * @param QueryBuilderInterface $builder
     * @return $this
     */
    public function withSaveQueryBuilder(QueryBuilderInterface $builder): self
    {
        return $this;
    }

    /**
     * Create a Writer service with a custom update query builder.
     *
     * @param QueryBuilderInterface $builder
     * @return $this
     */
    public function withUpdateQueryBuilder(QueryBuilderInterface $builder): self
    {
        return $this;
    }

    /**
     * Create a Writer service with a custom update query builder.
     *
     * @param ResultBuilder $builder
     * @return $this
     */
    public function withResultBuilder(ResultBuilder $builder): self
    {
        return $this;
    }


    /**
     * Update is not supported.
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
