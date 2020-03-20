<?php

declare(strict_types=1);

namespace Jasny\DB\Schema;

use Jasny\DB\Map\MapInterface;
use Jasny\DB\Map\SchemaMap;

/**
 * Database schema with field maps and relationships.
 */
interface SchemaInterface
{
    /**
     * Create a schema mapped for the collection / table.
     */
    public function map(string $collection): SchemaMap;

    /**
     * Get the field map of the collection / table.
     */
    public function getMapOf(string $collection): MapInterface;

    /**
     * Get all relationships of a collection / table.
     *
     * @param string $collection
     * @return Relationship[]
     */
    public function getRelationships(string $collection): array;

    /**
     * Get single relationship for a field.
     *
     * @throws \UnexpectedValueException  If no or more than one relationship matches
     */
    public function getRelationship(
        string $collection,
        ?string $field,
        ?string $related = null,
        ?string $relatedField = null
    ): Relationship;
}
