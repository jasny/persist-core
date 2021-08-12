<?php

declare(strict_types=1);

namespace Jasny\Persist\Schema;

use Jasny\Persist\Exception\NoRelationshipException;
use Jasny\Persist\Map\MapInterface;
use Jasny\Persist\Map\SchemaMap;

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
     * @param string                    $collection  Name of left-hand table / collection
     * @param string                    $related     Name of right-hand table / collection
     * @param array<string,string>|null $match       Field pairs as ON in JOIN statement
     * @return Relationship
     * @throws NoRelationshipException  If no or more than one relationship matches
     */
    public function getRelationship(string $collection, string $related, ?array $match = null): Relationship;

    /**
     * Get the relationship for a field of a collection.
     *
     * @throws NoRelationshipException  If no or more than one relationship matches.
     */
    public function getRelationshipForField(string $collection, string $field): Relationship;


    /**
     * Get child relationships of a collection / table.
     *
     * @param string $collection
     * @return Relationship[]
     */
    public function getChildren(string $collection): array;

    /**
     * Get a child relationship by field name.
     *
     * @throws NoRelationshipException  If there is no child relationship with the given field name.
     */
    public function getChildForField(string $collection, string $field): Relationship;


    /**
     * Get the embedded relationship for a field of a collection.
     *
     * @param string $collection
     * @return Embedded[]
     */
    public function getEmbedded(string $collection): array;

    /**
     * Get the embedded relationship for a field of a collection.
     *
     * @throws NoRelationshipException  If field doesn't hold embedded entities.
     */
    public function getEmbeddedForField(string $collection, string $field): Embedded;
}
