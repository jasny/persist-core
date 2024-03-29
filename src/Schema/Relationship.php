<?php

declare(strict_types=1);

namespace Jasny\Persist\Schema;

use Jasny\Immutable;
use function Jasny\str_before;

/**
 * Relationship between two tables / collections.
 */
final class Relationship
{
    use Immutable\With;

    public const ONE_TO_ONE = 0b00;
    public const ONE_TO_MANY = 0b01;
    public const MANY_TO_ONE = 0b10;
    public const MANY_TO_MANY = 0b11;

    protected int $type;

    protected string $collection;
    protected string $relatedCollection;
    protected string $alias;
    protected string $relatedAlias;
    protected string $fieldName;
    protected JoinInterface $join;

    /**
     * @param int                                 $type        One of the relationship constants
     * @param string                              $collection  Name of left-hand table / collection
     * @param string                              $related     Name of right-hand table / collection
     * @param array<string,string>|JoinInterface  $join
     */
    public function __construct(int $type, string $collection, string $related, array|JoinInterface $join)
    {
        if ($type < 0 || $type > 0b11) {
            throw new \InvalidArgumentException("Invalid relationship type '$type'; use one of the constants");
        }

        $this->type = $type;

        $this->collection = str_before($collection, ':');
        $this->relatedCollection = str_before($related, ':');
        $this->alias = $collection;
        $this->relatedAlias = $related;
        $this->join = $join instanceof JoinInterface ? $join : new Join($join);
    }

    /**
     * Swap left and right sides.
     */
    public function swapped(): self
    {
        $copy = clone $this;
        $copy->type = (($this->type << 1) & 3) | (($this->type >> 1) & 3); // swap bit 0 and 1

        $copy->collection = $this->relatedCollection;
        $copy->relatedCollection = $this->collection;
        $copy->alias = $this->relatedAlias;
        $copy->relatedAlias = $this->alias;

        unset($copy->fieldName);
        $copy->join = $this->join->swapped();

        return $copy;
    }

    /**
     * See if the relationship matches the search criteria.
     */
    public function matches(string $collection, string $related): bool
    {
        return $this->alias === $collection && $this->relatedAlias === $related;
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
     * Get the local table / collection.
     */
    public function getCollection(): string
    {
        return $this->collection;
    }

    /**
     * Get the foreign table / collection.
     */
    public function getRelatedCollection(): string
    {
        return $this->relatedCollection;
    }

    /**
     * Get the local table / collection with alias.
     */
    public function getName(): string
    {
        return $this->alias;
    }

    /**
     * Get the foreign table / collection with alias.
     */
    public function getRelatedAlias(): string
    {
        return $this->alias;
    }

    /**
     * Field pairs as ON in a JOIN statement
     */
    public function getJoin(): JoinInterface
    {
        return $this->join;
    }


    /**
     * Set the field name.
     */
    public function withFieldName(string $fieldName): self
    {
        return $this->withProperty('fieldName', $fieldName);
    }

    /**
     * Get the field name.
     */
    public function getFieldName(): string
    {
        if (!isset($this->fieldName)) {
            throw new \BadMethodCallException("Field name not set");
        }

        return $this->fieldName;
    }
}
