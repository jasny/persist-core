<?php

declare(strict_types=1);

namespace Jasny\DB\Gateway;

use Jasny\DB\CRUD\CRUDInterface;
use Jasny\DB\CRUD\EntityNotFoundException;
use Jasny\DB\Fetcher\FetcherInterface;
use Jasny\DB\Search\SearchInterface;
use Jasny\Entity\EntityInterface;
use Jasny\EntityCollection\EntityCollectionInterface;
use Jasny\Entity\Trigger\TriggerSet;
use Jasny\IteratorPipeline\Pipeline;


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
     * @var TriggerManager
     */
    protected $triggers;

    /**
     * @var CRUDInterface
     */
    protected $crud;

    /**
     * @var FetcherInterface
     */
    protected $fetcher;

    /**
     * @var SearchInterface`
     */
    protected $search;


    /**
     * CompositeGateway constructor.
     *
     * @param mixed            $source
     * @param TriggerSet       $triggers
     * @param CRUDInterface    $crud      Service for CRUD and ORM/ODM
     * @param FetcherInterface $fetcher   Service to fetch data
     * @param SearchInterface  $search    Service for full text search
     */
    public function __construct(
        $source,
        TriggerSet $triggers,
        CRUDInterface $crud,
        FetcherInterface $fetcher,
        SearchInterface $search
    ) {
        $this->source = $source;
        $this->triggers = $triggers;

        $this->crud = $crud instanceof PrototypeInterface ? $crud->withSource($source) : $crud;
        $this->fetcher = $fetcher instanceof PrototypeInterface ? $fetcher->withSource($source) : $fetcher;
        $this->search = $search instanceof PrototypeInterface ? $search->withSource($source) : $search;
    }


    /**
     * Prepare entity, like setting triggers.
     *
     * @param EntityInterface $entity
     * @return void
     */
    protected function addEventsToEntity(EntityInterface $entity): void
    {
        $dispatcher = $this->triggers->getDispatcher();
        $entity->setEventDispatcher($dispatcher);
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
        $this->addEventsToEntity($entity);

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
        $this->addEventsToEntity($entity);

        return $entity;
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
        $set = $this->fetcher->fetchAll($filter, $opts);

        $set->apply(function(EntityInterface $entity) {
            $this->addEventsToEntity($entity);
        });

        return $set;
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
     * Fetch data and make it available through an iterator
     *
     * @param array $filter
     * @param array $opts
     * @return Pipeline
     */
    public function fetchData(array $filter = [], array $opts = []): Pipeline
    {
        return $this->fetcher->fetchData($filter, $opts);
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
        return $this->fetcher->count($filter, $opts);
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
