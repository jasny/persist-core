<?php

declare(strict_types=1);

namespace Jasny\Persist\Filter;

/**
 * Representation of an item of filter passed to a query builder.
 */
class FilterItem
{
    protected string $field;
    protected string $operator;
    protected mixed $value;

    /**
     * FilterItem constructor.
     */
    public function __construct(string $field, string $operator, mixed $value)
    {
        $this->field = $field;
        $this->operator = $operator;
        $this->value = $value;
    }

    /**
     * Get the field name.
     */
    public function getField(): string
    {
        return $this->field;
    }

    /**
     * Get the filter operator.
     */
    public function getOperator(): string
    {
        return $this->operator;
    }

    /**
     * Get the filter value.
     */
    public function getValue(): mixed
    {
        return $this->value;
    }
}
