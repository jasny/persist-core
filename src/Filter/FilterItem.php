<?php

declare(strict_types=1);

namespace Jasny\DB\Filter;

/**
 * Representation of an item of filter passed to a query builder.
 */
class FilterItem
{
    protected string $field;
    protected string $operator;

    /** @var mixed */
    protected $value;


    /**
     * FilterItem constructor.
     *
     * @param string $field
     * @param string $operator
     * @param mixed $value
     */
    public function __construct(string $field, string $operator, $value)
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
     *
     * @return mixed
     */
    public function getValue()
    {
        return $this->value;
    }
}
