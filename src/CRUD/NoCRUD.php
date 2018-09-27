<?php

declare(strict_types=1);

namespace Jasny\DB\CRUD;

use Jasny\DB\Exception\UnsupportedFeatureException;
use Jasny\Entity\EntityInterface;
use Jasny\EntityCollection\EntityCollectionInterface;

/**
 * CRUD is not supported
 */
class NoCRUD implements CRUDInterface
{
    /**
     * Create a new entity.
     *
     * @return EntityInterface
     * @throws UnsupportedFeatureException
     */
    public function create(...$args): EntityInterface
    {
        throw new UnsupportedFeatureException("CRUD is not supported");
    }

    /**
     * Fetch a single entity.
     *
     * @param mixed $id ID or filter
     * @param array $opts
     * @return EntityInterface
     * @throws UnsupportedFeatureException
     */
    public function fetch($id, array $opts = []): ?EntityInterface
    {
        throw new UnsupportedFeatureException("CRUD is not supported");
    }

    /**
     * Fetch multiple entities
     *
     * @param array $filter
     * @param array $opts
     * @return EntityCollectionInterface|EntityInterface[]
     * @throws UnsupportedFeatureException
     */
    public function fetchAll(array $filter, array $opts = []): EntityCollectionInterface
    {
        throw new UnsupportedFeatureException("CRUD is not supported");
    }

    /**
     * Check if an exists in the collection.
     *
     * @param mixed $id ID or filter
     * @param array $opts
     * @return bool
     * @throws UnsupportedFeatureException
     */
    public function exists($id, array $opts = []): bool
    {
        throw new UnsupportedFeatureException("CRUD is not supported");
    }

    /**
     * Save the entity
     *
     * @param EntityInterface $entity
     * @param array $opts
     * @return void
     * @throws UnsupportedFeatureException
     */
    public function save(EntityInterface $entity, array $opts = []): void
    {
        throw new UnsupportedFeatureException("CRUD is not supported");
    }

    /**
     * Delete the entity
     *
     * @param EntityInterface $entity
     * @param array $opts
     * @return void
     * @throws UnsupportedFeatureException
     */
    public function delete(EntityInterface $entity, array $opts = []): void
    {
        throw new UnsupportedFeatureException("CRUD is not supported");
    }
}
