<?php

declare(strict_types=1);

namespace Persist\Schema;

/**
 * Field with embedded relationship for collection.
 */
final class Embedded
{
    public const ONE_TO_ONE = 0b00;
    public const ONE_TO_MANY = 0b01;

    protected int $type;

    protected string $collection;
    protected string $field;


    /**
     * @param int    $type        One of the embedded relationship constants.
     * @param string $collection  Name of table / collection
     * @param string $field       Field name
     */
    public function __construct(int $type, string $collection, string $field)
    {
        if ($type < 0 || $type > 0b01) {
            throw new \InvalidArgumentException("Invalid relationship type '$type'; use one of the constants");
        }

        $this->type = $type;
        $this->collection = $collection;
        $this->field = $field;
    }

    /**
     * Get the relationship type.
     *
     * @return int  class constant: ONE_TO_ONE, ONE_TO_MANY.
     */
    public function getType(): int
    {
        return $this->type;
    }

    /**
     * Does the field hold many embedded document (as array)?
     */
    public function isToMany(): bool
    {
        return (bool)($this->type & 0b01);
    }


    /**
     * Get the table / collection.
     */
    public function getCollection(): string
    {
        return $this->collection;
    }

    /**
     * Get the field with embedded entities.
     */
    public function getField(): string
    {
        return $this->field;
    }
}
