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
     * Get the schema mapped for the collection / table.
     */
    public function map(string $collection): SchemaMap;

    /**
     * Get the field map for the collection / table.
     */
    public function getMap(string $collection): MapInterface;

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
     * @param string          $collection
     * @param string|string[] $field
     * @param string          $related
     * @param string|string[] $relatedFields
     * @return Relationship
     * @throws \UnexpectedValueException  If no or more than one relationship matches
     */
    public function getRelationship(string $collection, $field, string $related, $relatedFields): Relationship;
}
