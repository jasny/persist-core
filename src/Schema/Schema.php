<?php

declare(strict_types=1);

namespace Jasny\Persist\Schema;

use Improved\IteratorPipeline\Pipeline;
use Jasny\Persist\Exception\NoRelationshipException;
use Jasny\Persist\Map\FieldMap;
use Jasny\Persist\Map\MapInterface;
use Jasny\Persist\Map\NestedMap;
use Jasny\Persist\Map\SchemaMap;
use Jasny\Persist\Map\NoMap;
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

    /** @var array<string,array<string,Embedded>> */
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
    public function withDefaultMap(MapInterface|array $map): static
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
    public function withMap(string $collection, MapInterface|array $map): static
    {
        return $this->withPropertyKey('maps', $collection, $this->createMap($map));
    }

    /**
     * Create a map.
     *
     * @param MapInterface|array<string,string|false> $map
     * @return MapInterface
     */
    protected function createMap(MapInterface|array $map): MapInterface
    {
        return $map instanceof MapInterface ? $map : new FieldMap($map);
    }


    /**
     * Get a copy with a new relationship between two collections / tables.
     */
    public function withRelationship(Relationship $relationship): static
    {
        $clone = clone $this;

        $clone->relationships[$relationship->getCollection()][] = $relationship;
        $clone->relationships[$relationship->getRelatedCollection()][] = $relationship->swapped();

        return $clone;
    }

    /**
     * Get a copy with a one to one relationship between two collections / tables.
     *
     * @param string               $collection1  Name of left-hand table / collection
     * @param string               $collection2  Name of right-hand table / collection
     * @param array<string,string> $match        Field pairs as ON in JOIN statement
     * @return static
     */
    final public function withOneToOne(string $collection1, string $collection2, array $match): static
    {
        return $this->withRelationship(
            new Relationship(Relationship::ONE_TO_ONE, $collection1, $collection2, $match)
        );
    }

    /**
     * Get a copy with a one to one relationship between two collections / tables.
     *
     * @param string               $collection1  Name of left-hand table / collection
     * @param string               $collection2  Name of right-hand table / collection
     * @param array<string,string> $match        Field pairs as ON in JOIN statement
     * @return static
     */
    final public function withOneToMany(string $collection1, string $collection2, array $match): static
    {
        return $this->withRelationship(
            new Relationship(Relationship::ONE_TO_MANY, $collection1, $collection2, $match)
        );
    }

    /**
     * Get a copy with a one to one relationship between two collections / tables.
     *
     * @param string               $collection1  Name of left-hand table / collection
     * @param string               $collection2  Name of right-hand table / collection
     * @param array<string,string> $match        Field pairs as ON in JOIN statement
     * @return static
     */
    final public function withManyToOne(string $collection1, string $collection2, array $match): static
    {
        return $this->withRelationship(
            new Relationship(Relationship::MANY_TO_ONE, $collection1, $collection2, $match)
        );
    }

    /**
     * Get a copy with a one to one relationship between two collections / tables.
     *
     * @param string               $collection1  Name of left-hand table / collection
     * @param string               $collection2  Name of right-hand table / collection
     * @param array<string,string> $match        Field pairs as ON in JOIN statement
     * @return static
     */
    final public function withManyToMany(string $collection1, string $collection2, array $match): static
    {
        return $this->withRelationship(
            new Relationship(Relationship::MANY_TO_MANY, $collection1, $collection2, $match)
        );
    }


    /**
     * Get a copy with a new embedded relationship for a collection.
     */
    public function withEmbedded(Embedded $embedded): static
    {
        $clone = clone $this;
        $clone->embedded[$embedded->getCollection()][$embedded->getField()] = $embedded;

        return $clone;
    }

    /**
     * Get a copy with an one to one embedded relationship for a collection.
     */
    final public function withOneEmbedded(string $collection, string $field): static
    {
        return $this->withEmbedded(new Embedded(Embedded::ONE_TO_ONE, $collection, $field));
    }

    /**
     * Get a copy with an one to many embedded relationship for a collection.
     */
    final public function withManyEmbedded(string $collection, string $field): static
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
        return $this->nestEmbeddedMaps(
            $this->maps[$collection] ?? $this->defaultMap,
            $collection
        );
    }

    /**
     * Nest maps for embedded relationships.
     */
    private function nestEmbeddedMaps(MapInterface $map, string $collection): MapInterface
    {
        foreach (($this->embedded[$collection] ?? []) as $embedded) {
            $field = $embedded->getField();

            $childMap = $this->nestEmbeddedMaps(
                $this->maps["$collection.$field"] ?? new NoMap(), // Embedded fields don't get default map
                "$collection.$field"
            );

            // Don't skip if child map is a NoMap. The to-many mapped field is important for lookup/hydrate.

            $map = ($map instanceof NestedMap ? $map : new NestedMap($map))
                ->withMappedField($field . ($embedded->isToMany() ? '[]' : ''), $childMap);
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
                " found for field '$field' of '{$collection}'"
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
            throw new NoRelationshipException("No embedded relationship found for field '{$field}' of '{$collection}'");
        }

        return $this->embedded[$collection][$field];
    }
}
