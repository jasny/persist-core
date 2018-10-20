<?php

declare(strict_types=1);

namespace Jasny\DB\Write;

use Jasny\DB\QueryBuilder\QueryBuilderInterface;
use Jasny\DB\Exception\UnsupportedFeatureException;
use Jasny\DB\Update\UpdateOperation;

/**
 * Writing to storage is not supported.
 */
class NoWrite implements WriterInterface
{
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
     * Update is not supported
     *
     * @param mixed                             $storage
     * @param array                             $filter
     * @param UpdateOperation|UpdateOperation[] $changes
     * @param array                             $opts
     * @return void
     */
    public function update($storage, array $filter, $changes, array $opts = []): void
    {
        throw new UnsupportedFeatureException("Writing to storage is not supported");
    }

    /**
     * Save is not supported
     *
     * @param mixed    $storage
     * @param iterable $items
     * @param array    $opts
     * @return iterable
     * @throws UnsupportedFeatureException
     */
    public function save($storage, iterable $items, array $opts = []): iterable
    {
        throw new UnsupportedFeatureException("Writing to storage is not supported");
    }

    /**
     * Delete is not supported
     *
     * @param mixed $storage
     * @param array $filter
     * @param array $opts
     * @return void
     * @throws UnsupportedFeatureException
     */
    public function delete($storage, array $filter, array $opts = []): void
    {
        throw new UnsupportedFeatureException("Writing to storage is not supported");
    }
}
