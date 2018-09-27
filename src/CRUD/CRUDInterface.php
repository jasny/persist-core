<?php

declare(strict_types=1);

namespace Jasny\DB\CRUD;

use Jasny\DB\Exception\EntityNotFoundException;
use Jasny\Entity\EntityInterface;
use Jasny\EntityCollection\EntityCollectionInterface;

/**
 * Interface for Database CRUD plus ORM / ODM.
 */
interface CRUDInterface
{
    /**
     * Create a new entity.
     *
     * @param mixed ...$args   Arguments are passed to entity constructor
     * @return EntityInterface
     */
    public function create(...$args): EntityInterface;

    /**
     * Fetch a single entity.
     *
     * @param mixed $id    ID or filter
     * @param array $opts
     * @return EntityInterface
     * @throws EntityNotFoundException if Entity with id isn't found and no 'optional' opt was given
     */
    public function fetch($id, array $opts = []): ?EntityInterface;

    /**
     * Fetch multiple entities
     *
     * @param array $filter
     * @param array $opts
     * @return EntityCollectionInterface|EntityInterface[]
     */
    public function fetchAll(array $filter, array $opts = []): EntityCollectionInterface;

    /**
     * Check if an exists in the collection.
     *
     * @param mixed $id   ID or filter
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
