<?php

declare(strict_types=1);

namespace Jasny\DB\Update;

use function Jasny\expect_type;

/**
 * Operation for update query
 */
class UpdateOperation
{
    /**
     * @var string
     */
    protected $operator;

    /**
     * @var string|array
     */
    protected $field;

    /**
     * @var mixed
     */
    protected $value;


    /**
     * Class constructor.
     *
     * @param string       $operator
     * @param string|array $field
     * @param mixed        $value
     */
    public function __construct(string $operator, $field, $value)
    {
        expect_type($field, ['string', 'array']);

        $this->operator = $operator;
        $this->field = $field;
        $this->value = $value;
    }


    /**
     * Get the operator.
     *
     * @return string
     */
    public function getOperator()
    {
        return $this->operator;
    }

    /**
     * Get the field name.
     *
     * @return string
     */
    public function getField()
    {
        return $this->field;
    }

    /**
     * Get the operator value.
     *
     * @return mixed
     */
    public function getValue()
    {
        return $this->value;
    }
}
