<?php

declare(strict_types=1);

namespace Jasny\DB\CRUD;

use Jasny\DB\CRUDInterface;
use Jasny\DB\EntityNotFoundException;
use Jasny\EntityInterface;

/**
 * CRUD is not supported
 */
class NoCRUD implements CRUDInterface
{
    /**
     * Create a new entity.
     *
     * @return EntityInterface
     */
    public function create(): ?EntityInterface
    {
        throw new \BadMethodCallException("CRUD is not supported");
    }

    /**
     * Fetch a single entity.
     *
     * @param mixed $id ID or filter
     * @param array $opts
     * @return EntityInterface|null
     * @throws EntityNotFoundException if Entity with id isn't found and no 'optional' opt was given
     */
    public function fetch($id, array $opts = []): ?EntityInterface
    {
        throw new \BadMethodCallException("CRUD is not supported");
    }

    /**
     * Check if an exists in the collection.
     *
     * @param mixed $id ID or filter
     * @param array $opts
     * @return bool
     */
    public function exists($id, array $opts = []): bool
    {
        throw new \BadMethodCallException("CRUD is not supported");
    }

    /**
     * Save the entity
     *
     * @param EntityInterface $entity
     * @param array $opts
     * @return void
     */
    public function save(EntityInterface $entity, array $opts = []): void
    {
        throw new \BadMethodCallException("CRUD is not supported");
    }

    /**
     * Delete the entity
     *
     * @param EntityInterface $entity
     * @param array $opts
     * @return void
     */
    public function delete(EntityInterface $entity, array $opts = []): void
    {
        throw new \BadMethodCallException("CRUD is not supported");
    }
}
