<?php

declare(strict_types=1);

namespace Jasny\DB\Schema;

use Jasny\DB\Exception\NoRelationshipException;
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
     * Get a single relationship between two collections / tables.
     *
     * @param string                    $collection  Name of left hand table / collection
     * @param string                    $related     Name of right hand table / collection
     * @param array<string,string>|null $match       Field pairs as ON in JOIN statement
     * @return Relationship
     * @throws NoRelationshipException  If no or more than one relationship matches
     */
    public function getRelationship(string $collection, string $related, ?array $match = null): Relationship;

    /**
     * Get the relationship for a field of a collection.
     *
     * @throws NoRelationshipException  If no or more than one relationship matches
     */
    public function getRelationshipForField(string $collection, string $field): Relationship;
}
