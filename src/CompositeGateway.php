<?php

declare(strict_types=1);

namespace Jasny\DB;

use Jasny\DB\CRUD\CRUDInterface;
use Jasny\DB\CRUD\EntityNotFoundException;
use Jasny\DB\Fetch\FetchInterface;
use Jasny\DB\Search\SearchInterface;
use Jasny\EntityCollection\EntityCollectionInterface;
use Jasny\EntityInterface;


/**
 * Base class for composite gateway.
 */
class CompositeGateway implements GatewayInterface
{
    /**
     * @var mixed
     */
    protected $source;

    /**
     * @var EntityEvents
     */
    protected $events;

    /**
     * @var CRUDInterface
     */
    protected $crud;

    /**
     * @var FetchInterface
     */
    protected $fetch;

    /**
     * @var SearchInterface`
     */
    protected $search;


    /**
     * CompositeGateway constructor.
     *
     * @param mixed           $source
     * @param EntityEvents    $events
     * @param CRUDInterface   $crud
     * @param FetchInterface  $fetch
     * @param SearchInterface $search
     */
    public function __construct(
        $source,
        TriggerSet $events,
        CRUDInterface $crud,
        FetchInterface $fetch,
        SearchInterface $search
    ) {
        $this->source = $source;
        $this->events = $events;

        $this->crud = $crud instanceof PrototypeInterface ? $crud->withSource($source) : $crud;
        $this->fetch = $fetch instanceof PrototypeInterface ? $fetch->withSource($source) : $fetch;
        $this->search = $search instanceof PrototypeInterface ? $search->withSource($source) : $search;
    }


    /**
     * Prepare entity, like setting events.
     *
     * @param EntityInterface $entity
     * @return void
     */
    protected function prepareEntity(EntityInterface $entity): void
    {
        $this->events->applyTo($entity);
    }


    /**
     * Create a new entity.
     *
     * @param mixed ...$params
     * @return EntityInterface
     */
    public function create(...$params): EntityInterface
    {
        $entity = $this->crud->create(...$params);
        $this->prepareEntity($entity);

        return $entity;
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
        $entity = $this->crud->fetch($id, $opts);
        $this->prepareEntity($entity);

        return $entity;
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
        return $this->crud->exists($id, $opts);
    }

    /**
     * Save the entity
     *
     * @param EntityInterface $entity
     * @param array           $opts
     * @return void
     */
    public function save(EntityInterface $entity, array $opts = []): void
    {
        $this->crud->save($entity, $opts);
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
        $this->crud->delete($entity, $opts);
    }

    /**
     * Fetch all entities from the set.
     *
     * @param array $filter
     * @param array $opts
     * @return EntityCollectionInterface|EntityInterface\[]
     */
    public function fetchAll(array $filter = [], array $opts = []): EntityCollectionInterface
    {
        $set = $this->fetch->fetchAll($filter, $opts);

        $set->apply(function(EntityInterface $entity) {
            $this->prepareEntity($entity);
        });

        return $set;
    }

    /**
     * Fetch data and make it available through an iterator
     *
     * @param array $filter
     * @param array $opts
     * @return iterable
     */
    public function fetchList(array $filter = [], array $opts = []): iterable
    {
        return $this->fetch->fetchList($filter, $opts);
    }

    /**
     * Fetch id/description pairs.
     *
     * @param array $filter
     * @param array $opts
     * @return array
     */
    public function fetchPairs(array $filter = [], array $opts = []): array
    {
        return $this->fetch->fetchPairs($filter, $opts);
    }

    /**
     * Fetch the number of entities in the set.
     *
     * @param array $filter
     * @param array $opts
     * @return int
     */
    public function count(array $filter = [], array $opts = []): int
    {
        return $this->fetch->count($filter, $opts);
    }


    /**
     * SearchInterface entities.
     *
     * @param string $terms
     * @param array $filter
     * @param array $opts
     * @return EntityCollectionInterface|EntityInterface[]
     */
    public function search($terms, array $filter = [], array $opts = []): EntityCollectionInterface
    {
        return $this->search->search();
    }
}
