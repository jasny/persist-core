<?php

declare(strict_types=1);

namespace Persist\Schema;

use Improved\IteratorPipeline\Pipeline;
use Persist\Exception\NoRelationshipException;
use Persist\Map\FieldMap;
use Persist\Map\MapInterface;
use Persist\Map\NestedMap;
use Persist\Map\SchemaMap;
use Persist\Map\NoMap;
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

    /** @var array<string,array<int,Relationship>> */
    protected array $relationships = [];

    /** @var array<string,array<string,Relationship>> */
    protected array $embedded = [];


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
     * Get a copy with a new embedded relationship for a collection.
     *
     * @param Embedded $embedded
     * @return static
     */
    public function withEmbedded(Embedded $embedded): self
    {
        $clone = clone $this;
        $clone->embedded[$embedded->getCollection()][$embedded->getField()] = $embedded;

        return $clone;
    }

    /**
     * Get a copy with an one to one embedded relationship for a collection.
     *
     * @param string $collection
     * @param string $field
     * @return static
     */
    final public function withOneEmbedded(string $collection, string $field): self
    {
        return $this->withEmbedded(new Embedded(Embedded::ONE_TO_ONE, $collection, $field));
    }

    /**
     * Get a copy with an one to many embedded relationship for a collection.
     *
     * @param string $collection
     * @param string $field
     * @return static
     */
    final public function withManyEmbedded(string $collection, string $field): self
    {
        return $this->withEmbedded(new Embedded(Embedded::ONE_TO_MANY, $collection, $field));
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
        $map = $this->maps[$collection] ?? $this->defaultMap;

        /** @var Embedded $embedded */
        foreach (($this->embedded[$collection] ?? []) as $embedded) {
            $field = $embedded->getField();

            if (!isset($this->maps["$collection.$field"])) {
                continue;
            }

            $map = ($map instanceof NestedMap ? $map : new NestedMap($map))
                ->withMappedField($field, $this->getMapOf("$collection.$field"));
        }

        return $map;
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
                " found for {$collection}.{$field}"
            );
        }

        return $relationships[0];
    }


    /**
     * @inheritDoc
     */
    public function getEmbedded(string $collection): array
    {
        return array_values($this->embedded[$collection] ?? []);
    }

    /**
     * @inheritDoc
     */
    public function getEmbeddedForField(string $collection, string $field): Embedded
    {
        if (!isset($this->embedded[$collection][$field])) {
            throw new NoRelationshipException("No embedded relationship found for {$collection}.{$field}");
        }

        return $this->embedded[$collection][$field];
    }
}
