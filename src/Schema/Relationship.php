<?php

declare(strict_types=1);

namespace Jasny\DB\Schema;

use Improved as i;

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
    protected array $fields;
    protected string $relatedCollection;
    protected array $relatedFields;

    /**
     * @param int             $type          One of the relationship constants
     * @param string          $collection    Name of left hand table / collection
     * @param string|string[] $field         Field(s) of left hand table / collection with primary or foreign key
     * @param string          $related       Name of right hand table / collection
     * @param string|string[] $relatedField  Field of right hand table / collection with primary or foreign key
     */
    public function __construct(int $type, string $collection, $field, string $related, $relatedField)
    {
        if ($type > 0b11) {
            throw new \InvalidArgumentException("Invalid relationship type '$type'; use one of the constants");
        }

        $this->type = $type;

        $this->collection = $collection;
        $this->fields = $this->fieldToFields($field);

        $this->relatedCollection = $related;
        $this->relatedFields = $this->fieldToFields($relatedField);
    }

    /**
     * @param string|string[] $field
     * @return string[]
     */
    private function fieldToFields($field): array
    {
        if (is_array($field)) {
            i\iterable_walk(i\iterable_type_check($field, 'string'));
            return $field;
        }

        i\type_check($field, 'string');
        return [$field];
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
        $copy->fields = $this->relatedFields;

        $copy->relatedCollection = $this->collection;
        $copy->relatedFields = $this->fields;

        return $copy;
    }

    /**
     * See if the relationship matches the search criteria.
     * Null means "don't care".
     *
     * @param string|null          $collection
     * @param string|string[]|null $field1
     * @param string|null          $relatedCollection
     * @param string|string[]|null $field2
     * @return bool
     */
    public function matches(?string $collection, $field1, ?string $relatedCollection, $field2): bool
    {
        return
            ($collection === null || $this->collection === $collection) &&
            ($relatedCollection === null || $this->relatedCollection === $relatedCollection) &&
            ($field1 === null || $this->fields === is_array($field1) ? $field1 : [$field1]) &&
            ($field2 === null || $this->relatedFields === is_array($field2) ? $field2 : [$field2]);
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
     * Get fields of the left hand table / collection.
     *
     * @return string[]
     */
    public function getFields(): array
    {
        return $this->fields;
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
     *
     * @return string[]
     */
    public function getRelatedFields(): array
    {
        return $this->relatedFields;
    }
}
