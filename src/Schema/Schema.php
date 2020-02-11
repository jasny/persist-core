<?php

declare(strict_types=1);

namespace Jasny\DB\Schema;

use Improved\IteratorPipeline\Pipeline;
use Jasny\DB\Map\ConfiguredMap;
use Jasny\DB\Map\FieldMap;
use Jasny\DB\Map\MapInterface;
use Jasny\DB\Map\SchemaMap;
use Jasny\DB\Map\NoMap;
use Jasny\Immutable;

/**
 * Representation of database schema with field maps and relationships.
 * @immutable
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
     * @param string          $collection1  Name of left hand table / collection
     * @param string|string[] $field1       Field(s) of left hand table / collection with primary or foreign id
     * @param string          $collection2  Name of right hand table / collection
     * @param string|string[] $field2       Field of right hand table / collection with primary or foreign id
     * @return static
     */
    final public function withOneToOne(string $collection1, $field1, string $collection2, $field2): self
    {
        return $this->withRelationship(
            new Relationship(Relationship::ONE_TO_ONE, $collection1, $field1, $collection2, $field2)
        );
    }

    /**
     * Get a copy with a one to one relationship between two collections / tables.
     *
     * @param string          $collection1  Name of left hand table / collection
     * @param string|string[] $field1       Field(s) of left hand table / collection with primary or foreign id
     * @param string          $collection2  Name of right hand table / collection
     * @param string|string[] $field2       Field of right hand table / collection with primary or foreign id
     * @return static
     */
    final public function withOneToMany(string $collection1, $field1, string $collection2, $field2): self
    {
        return $this->withRelationship(
            new Relationship(Relationship::ONE_TO_MANY, $collection1, $field1, $collection2, $field2)
        );
    }

    /**
     * Get a copy with a one to one relationship between two collections / tables.
     *
     * @param string          $collection1  Name of left hand table / collection
     * @param string|string[] $field1       Field(s) of left hand table / collection with primary or foreign id
     * @param string          $collection2  Name of right hand table / collection
     * @param string|string[] $field2       Field of right hand table / collection with primary or foreign id
     * @return static
     */
    final public function withManyToOne(string $collection1, $field1, string $collection2, $field2): self
    {
        return $this->withRelationship(
            new Relationship(Relationship::MANY_TO_ONE, $collection1, $field1, $collection2, $field2)
        );
    }

    /**
     * Get a copy with a one to one relationship between two collections / tables.
     *
     * @param string          $collection1  Name of left hand table / collection
     * @param string|string[] $field1       Field(s) of left hand table / collection with primary or foreign id
     * @param string          $collection2  Name of right hand table / collection
     * @param string|string[] $field2       Field of right hand table / collection with primary or foreign id
     * @return static
     */
    final public function withManyToMany(string $collection1, $field1, string $collection2, $field2): self
    {
        return $this->withRelationship(
            new Relationship(Relationship::MANY_TO_MANY, $collection1, $field1, $collection2, $field2)
        );
    }


    /**
     * Get the schema mapped for the collection / table.
     */
    public function map(string $collection): SchemaMap
    {
        return new SchemaMap($collection, $this);
    }

    /**
     * Get the field map for the collection / table (without relationships).
     */
    public function getMap(string $collection): MapInterface
    {
        return $this->maps[$collection] ?? $this->defaultMap;
    }

    /**
     * Get all relationships of a collection / table.
     *
     * @param string $collection
     * @return array
     */
    public function getRelationships(string $collection): array
    {
        return $this->relationships[$collection] ?? [];
    }

    /**
     * Get single relationship for a field.
     *
     * @param string               $collection
     * @param string|string[]|null $field
     * @param string|null          $related
     * @param string|string[]|null $relatedFields
     * @return Relationship
     * @throws \UnexpectedValueException  If no or more than one relationship matches
     */
    public function getRelationship(string $collection, $field, ?string $related, $relatedFields): Relationship
    {
        if ($related === null && $relatedFields !== null) {
            throw new \InvalidArgumentException("Unable to match related field if no related collection is specified");
        }

        /** @var Relationship[] $relationships */
        $relationships = Pipeline::with($this->relationships[$collection])
            ->filter(fn(Relationship $rel) => $rel->matches($collection, $field, $related, $relatedFields))
            ->values()
            ->toArray();

        if (count($relationships) !== 1) {
            throw new \UnexpectedValueException(join("", [
                (count($relationships) === 0 ? "No relationship" : "Multiple relationships"),
                $related === null ? " found for " : " found between ",
                $collection,
                $field !== null ? ' (' . json_encode($field) . ')' : '',
                $related !== null ? " and {$related}" : '',
                $relatedFields !== null ? ' (' . json_encode($relatedFields) . ')' : '',
            ]));
        }

        return $relationships[0];
    }
}
