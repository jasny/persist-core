<?php

declare(strict_types=1);

namespace Jasny\DB\Schema;

use Improved\IteratorPipeline\Pipeline;
use Jasny\DB\Exception\NoRelationshipException;
use Jasny\DB\Map\FieldMap;
use Jasny\DB\Map\MapInterface;
use Jasny\DB\Map\SchemaMap;
use Jasny\DB\Map\NoMap;
use Jasny\Immutable;

/**
 * Representation of database schema with field maps and relationships.
 */
class Schema implements SchemaInterface
{
    use Immutable\With;

    protected MapInterface $defaultMap;

    /** @var array<string,MapInterface> */
    protected array $maps = [];

    /** @var array<string,array<Relationship>> */
    protected array $relationships = [];


    /**
     * Schema constructor.
     */
    public function __construct()
    {
        $this->defaultMap = new NoMap();
    }

    /**
     * Get a copy with a default field map.
     *
     * @param MapInterface|array<string,string|false> $map
     * @return static
     */
    public function withDefaultMap($map): self
    {
        return $this->withProperty('defaultMap', $this->createMap($map));
    }

    /**
     * Get a copy with a field map for a collection.
     *
     * @param string                                  $collection  Collection or table name
     * @param MapInterface|array<string,string|false> $map
     * @return static
     */
    public function withMap(string $collection, $map): self
    {
        return $this->withPropertyKey('maps', $collection, $this->createMap($map));
    }

    /**
     * Create a map.
     *
     * @param MapInterface|array<string,string|false> $map
     * @return MapInterface
     */
    protected function createMap($map): MapInterface
    {
        return $map instanceof MapInterface ? $map : new FieldMap($map);
    }

    /**
     * Get a copy with a new relationship between two collections / tables.
     *
     * @param Relationship $relationship
     * @return static
     */
    public function withRelationship(Relationship $relationship): self
    {
        $clone = clone $this;

        $clone->relationships[$relationship->getCollection()][] = $relationship;
        $clone->relationships[$relationship->getRelatedCollection()][] = $relationship->swapped();

        return $clone;
    }

    /**
     * Get a copy with a one to one relationship between two collections / tables.
     *
     * @param string               $collection1  Name of left hand table / collection
     * @param string               $collection2  Name of right hand table / collection
     * @param array<string,string> $match        Field pairs as ON in JOIN statement
     * @return static
     */
    final public function withOneToOne(string $collection1, string $collection2, array $match): self
    {
        return $this->withRelationship(
            new Relationship(Relationship::ONE_TO_ONE, $collection1, $collection2, $match)
        );
    }

    /**
     * Get a copy with a one to one relationship between two collections / tables.
     *
     * @param string               $collection1  Name of left hand table / collection
     * @param string               $collection2  Name of right hand table / collection
     * @param array<string,string> $match        Field pairs as ON in JOIN statement
     * @return static
     */
    final public function withOneToMany(string $collection1, string $collection2, array $match): self
    {
        return $this->withRelationship(
            new Relationship(Relationship::ONE_TO_MANY, $collection1, $collection2, $match)
        );
    }

    /**
     * Get a copy with a one to one relationship between two collections / tables.
     *
     * @param string               $collection1  Name of left hand table / collection
     * @param string               $collection2  Name of right hand table / collection
     * @param array<string,string> $match        Field pairs as ON in JOIN statement
     * @return static
     */
    final public function withManyToOne(string $collection1, string $collection2, array $match): self
    {
        return $this->withRelationship(
            new Relationship(Relationship::MANY_TO_ONE, $collection1, $collection2, $match)
        );
    }

    /**
     * Get a copy with a one to one relationship between two collections / tables.
     *
     * @param string               $collection1  Name of left hand table / collection
     * @param string               $collection2  Name of right hand table / collection
     * @param array<string,string> $match        Field pairs as ON in JOIN statement
     * @return static
     */
    final public function withManyToMany(string $collection1, string $collection2, array $match): self
    {
        return $this->withRelationship(
            new Relationship(Relationship::MANY_TO_MANY, $collection1, $collection2, $match)
        );
    }


    /**
     * @inheritDoc
     */
    public function map(string $collection): SchemaMap
    {
        return new SchemaMap($collection, $this);
    }

    /**
     * @inheritDoc
     */
    public function getMapOf(string $collection): MapInterface
    {
        return $this->maps[$collection] ?? $this->defaultMap;
    }

    /**
     * @inheritDoc
     */
    public function getRelationships(string $collection): array
    {
        return $this->relationships[$collection] ?? [];
    }

    /**
     * @inheritDoc
     */
    public function getRelationship(string $collection, string $related, ?array $match = null): Relationship
    {
        /** @var Relationship[] $relationships */
        $relationships = Pipeline::with($this->relationships[$collection] ?? [])
            ->filter(fn(Relationship $rel) => $rel->matches($collection, $related, $match))
            ->values()
            ->toArray();

        if (count($relationships) !== 1) {
            $matching = is_array($match)
                ? Pipeline::with($match)
                    ->map(fn($right, $left) => "{$collection}.{$left} = {$related}.{$right}")
                    ->concat(' and ')
                : null;

            throw new NoRelationshipException(
                (count($relationships) === 0 ? "No relationship" : "Multiple relationships") .
                " found between {$collection} and {$related}" .
                ($matching !== null ? " with ({$matching})" : '')
            );
        }

        return $relationships[0];
    }

    /**
     * @inheritDoc
     */
    public function getRelationshipForField(string $collection, string $field): Relationship
    {
        /** @var Relationship[] $relationships */
        $relationships = Pipeline::with($this->relationships[$collection] ?? [])
            ->filter(fn(Relationship $rel) => array_keys($rel->getMatch()) === [$field])
            ->values()
            ->toArray();

        if (count($relationships) !== 1) {
            throw new NoRelationshipException(
                (count($relationships) === 0 ? "No relationship" : "Multiple relationships") .
                " found for {$collection} ({$field})"
            );
        }

        return $relationships[0];
    }
}
