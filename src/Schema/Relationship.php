<?php

declare(strict_types=1);

namespace Jasny\DB\Schema;

use function Jasny\str_before;

/**
 * Relationship between two tables / collections.
 */
final class Relationship
{
    public const ONE_TO_ONE = 0b00;
    public const ONE_TO_MANY = 0b01;
    public const MANY_TO_ONE = 0b10;
    public const MANY_TO_MANY = 0b11;

    protected int $type;

    protected string $collection;
    protected string $relatedCollection;
    protected string $alias;
    protected string $relatedAlias;

    /** @var array<string,string> */
    protected array $match;

    /**
     * @param int                  $type        One of the relationship constants
     * @param string               $collection  Name of left hand table / collection
     * @param string               $related     Name of right hand table / collection
     * @param array<string,string> $match       Field pairs as ON in a JOIN statement
     */
    public function __construct(int $type, string $collection, string $related, array $match)
    {
        if ($type < 0 || $type > 0b11) {
            throw new \InvalidArgumentException("Invalid relationship type '$type'; use one of the constants");
        }

        $this->type = $type;

        $this->collection = str_before($collection, ':');
        $this->relatedCollection = str_before($related, ':');
        $this->alias = $collection;
        $this->relatedAlias = $related;
        $this->match = $match;
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
        $copy->relatedCollection = $this->collection;
        $copy->alias = $this->relatedAlias;
        $copy->relatedAlias = $this->alias;

        $copy->match = array_flip($this->match);

        return $copy;
    }

    /**
     * See if the relationship matches the search criteria.
     *
     * @param string                    $collection  Local name or alias.
     * @param string                    $related     Foreign name or alias.
     * @param array<string,string>|null $match       Field pairs. Null means "don't care".
     * @return bool
     */
    public function matches(string $collection, string $related, ?array $match = null): bool
    {
        return
            $this->alias === $collection &&
            $this->relatedAlias === $related &&
            (
                $match === null ||
                (count($match) === count($this->match) && array_diff_assoc($this->match, $match) === [])
            );
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
     *
     * @return array<string,string>
     */
    public function getMatch(): array
    {
        return $this->match;
    }
}
