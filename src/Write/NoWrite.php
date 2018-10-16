<?php

declare(strict_types=1);

namespace Jasny\DB\Write;

use Jasny\DB\QueryBuilder\QueryBuilderInterface;
use Jasny\DB\Exception\UnsupportedFeatureException;

/**
 * Writing to storage is not supported.
 */
class NoWrite implements WriterInterface
{
    /**
     * Create a CRUD service with a custom query builder.
     *
     * @param QueryBuilderInterface $queryBuilder
     * @return mixed
     */
    public function withQueryBuilder(QueryBuilderInterface $queryBuilder)
    {
        return $this;
    }


    /**
     * Update is not supported
     *
     * @param mixed     $storage
     * @param \stdClass $changes
     * @param array     $filter
     * @param array     $opts
     * @return void
     */
    public function update($storage, \stdClass $changes, array $filter, array $opts = []): void
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
    public function delete($storage, array $filter = null, array $opts = []): void
    {
        throw new UnsupportedFeatureException("Writing to storage is not supported");
    }
}
