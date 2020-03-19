<?php

declare(strict_types=1);

namespace Jasny\DB\Schema;

/**
 * Relationship between two classes.
 * @immutable
 */
final class Relationship
{
    public const ONE_TO_ONE = 0b00;
    public const ONE_TO_MANY = 0b01;
    public const MANY_TO_ONE = 0b10;
    public const MANY_TO_MANY = 0b11;

    protected int $type;

    protected string $collection;
    protected string $field;

    protected string $relatedCollection;
    protected string $relatedField;

    /**
     * @param int    $type          One of the relationship constants
     * @param string $collection    Name of left hand table / collection
     * @param string $field         Field(s) of left hand table / collection with primary or foreign key
     * @param string $related       Name of right hand table / collection
     * @param string $relatedField  Field of right hand table / collection with primary or foreign key
     */
    public function __construct(int $type, string $collection, string $field, string $related, string $relatedField)
    {
        if ($type > 0b11) {
            throw new \InvalidArgumentException("Invalid relationship type '$type'; use one of the constants");
        }

        $this->type = $type;

        $this->collection = $collection;
        $this->field = $field;

        $this->relatedCollection = $related;
        $this->relatedField = $relatedField;
    }

    /**
     * Swap left and right sides.
     *
     * @return static
     */
    public function swapped(): self
    {
        $copy = clone $this;
        $copy->type = (($this->type << 1) & 3) | (($this->type >> 1) & 3); // swap bit 0 and 1

        $copy->collection = $this->relatedCollection;
        $copy->field = $this->relatedField;

        $copy->relatedCollection = $this->collection;
        $copy->relatedField = $this->field;

        return $copy;
    }

    /**
     * See if the relationship matches the search criteria.
     * Null means "don't care".
     */
    public function matches(?string $collection, ?string $field, ?string $related, ?string $relatedField): bool
    {
        return
            ($collection === null || $this->collection === $collection) &&
            ($related === null || $this->relatedCollection === $related) &&
            ($field === null || $this->field === $field) &&
            ($relatedField === null || $this->relatedField === $relatedField);
    }


    /**
     * Get the relationship type.
     *
     * @return int  class constant: ONE_TO_ONE, ONE_TO_MANY, MANY_TO_ONE, MANY_TO_MANY.
     */
    public function getType(): int
    {
        return $this->type;
    }

    /**
     * Is it a many-to-one or many-to-many relationship?
     */
    public function isFromMany(): bool
    {
        return (bool)($this->type & 0b10);
    }

    /**
     * Is it a one-to-many or many-to-many relationship?
     */
    public function isToMany(): bool
    {
        return (bool)($this->type & 0b01);
    }


    /**
     * Get the left hand table / collection.
     */
    public function getCollection(): string
    {
        return $this->collection;
    }

    /**
     * Get field of the left hand table / collection.
     */
    public function getField(): string
    {
        return $this->field;
    }

    /**
     * Get the right hand table / collection.
     */
    public function getRelatedCollection(): string
    {
        return $this->relatedCollection;
    }

    /**
     * Get fields of the right hand table / collection.
     */
    public function getRelatedField(): string
    {
        return $this->relatedField;
    }
}
