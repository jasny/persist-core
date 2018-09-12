<?php

declare(strict_types=1);

namespace Jasny\DB\CRUD;

use Jasny\DB\PrototypeInterface;
use Jasny\EntityInterface;

/**
 * Interface for Database CRUD
 */
interface CRUDInterface
{
    /**
     * Create a new entity.
     *
     * @return EntityInterface
     */
    public function create(): EntityInterface;

    /**
     * Fetch a single entity.
     *
     * @param mixed $id    ID or filter
     * @param array $opts
     * @return EntityInterface|null
     * @throws EntityNotFoundException if Entity with id isn't found and no 'optional' opt was given
     */
    public function fetch($id, array $opts = []): ?EntityInterface;

    /**
     * Check if an exists in the collection.
     *
     * @param mixed $id  ID or filter
     * @param array $opts
     * @return bool
     */
    public function exists($id, array $opts = []): bool;

    /**
     * Save the entity
     *
     * @param EntityInterface $entity
     * @param array           $opts
     * @return void
     */
    public function save(EntityInterface $entity, array $opts = []): void;

    /**
     * Delete the entity
     *
     * @param EntityInterface $entity
     * @param array           $opts
     * @return void
     */
    public function delete(EntityInterface $entity, array $opts = []): void;
}