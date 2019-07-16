<?php

declare(strict_types=1);

namespace Jasny\DB\Write;

use Jasny\DB\QueryBuilder;
use Jasny\DB\Exception\UnsupportedFeatureException;
use Jasny\DB\Result;
use Jasny\DB\Update\UpdateOperation;
use Jasny\DB\Write;

/**
 * Writing to storage is not supported.
 */
class NoWrite implements Write, WithBuilders
{
    /**
     * Create a Writer service with a custom filter query builder.
     *
     * @param QueryBuilder $builder
     * @return $this
     */
    public function withQueryBuilder(QueryBuilder $builder): self
    {
        return $this;
    }

    /**
     * Create a Writer service with a custom builder pipeline for save.
     *
     * @param QueryBuilder $builder
     * @return $this
     */
    public function withSaveQueryBuilder(QueryBuilder $builder): self
    {
        return $this;
    }

    /**
     * Create a Writer service with a custom update query builder.
     *
     * @param QueryBuilder $builder
     * @return $this
     */
    public function withUpdateQueryBuilder(QueryBuilder $builder): self
    {
        return $this;
    }


    /**
     * Update is not supported
     *
     * @param mixed                             $storage
     * @param array                             $filter
     * @param UpdateOperation|UpdateOperation[] $changes
     * @param array                             $opts
     * @return Result
     */
    public function update($storage, array $filter, $changes, array $opts = []): Result
    {
        throw new UnsupportedFeatureException("Writing to storage is not supported");
    }

    /**
     * Save is not supported
     *
     * @param mixed    $storage
     * @param iterable $items
     * @param array    $opts
     * @return Result
     * @throws UnsupportedFeatureException
     */
    public function save($storage, iterable $items, array $opts = []): Result
    {
        throw new UnsupportedFeatureException("Writing to storage is not supported");
    }

    /**
     * Delete is not supported
     *
     * @param mixed $storage
     * @param array $filter
     * @param array $opts
     * @return Result
     * @throws UnsupportedFeatureException
     */
    public function delete($storage, array $filter, array $opts = []): Result
    {
        throw new UnsupportedFeatureException("Writing to storage is not supported");
    }
}
