<?php

declare(strict_types=1);

namespace Jasny\DB;

use Jasny\EntityInterface;
use Jasny\EntityCollectionInterface;

/**
 * Gateway to a data set, like a DB table (RDMS) or collection (NoSQL).
 */
interface GatewayInterface
{
    /**
     * Fetch a single entity.
     *
     * @param mixed $id    ID or filter
     * @param array $opts
     * @return Entity|null
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
     * @param Entity $entity
     * @param array  $opts
     * @return void
     */
    public function save(Entity $entity, array $opts = []): void;

    /**
     * Delete the entity
     *
     * @param Entity $entity
     * @param array  $opts
     * @return void
     */
    public function delete(Entity $entity, array $opts = []): void;


    /**
     * Fetch all entities from the set.
     *
     * @param array $filter
     * @param array $opts
     * @return EntityCollectionInterface|EntityInterface\[]
     */
    public function fetchAll(array $filter = [], array $opts = []): EntityCollectionInterface;

    /**
     * Fetch data and make it available through an iterator
     *
     * @param array $filter
     * @param array $opts
     * @return iterable
     */
    public function fetchList(array $filter = [], array $opts = []): iterable;

    /**
     * Fetch id/description pairs.
     *
     * @param array $filter
     * @param array $opts
     * @return array
     */
    public function fetchPairs(array $filter = [], array $opts = []): array;

    /**
     * Fetch the number of entities in the set.
     *
     * @param array $filter
     * @param array $opts
     * @return int
     */
    public function count(array $filter = [], array $opts = []): int;
}
