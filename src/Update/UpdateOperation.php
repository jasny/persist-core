<?php

declare(strict_types=1);

namespace Jasny\DB\Update;

/**
 * Operation for update query
 * @immutable
 */
class UpdateOperation
{
    const SUPPORTED_OPERATIONS = [
        'set',
        'patch',
        'inc',
        'mul',
        'div',
        'mod',
        'pull',
        'push',
    ];

    protected string $operator;
    protected array $pairs;

    /**
     * Class constructor.
     *
     * @param string              $operator  Update operator
     * @param array<string,mixed> $pairs     field/value pairs
     */
    public function __construct(string $operator, array $pairs)
    {
        if (!in_array($operator, static::SUPPORTED_OPERATIONS, true)) {
            throw new \InvalidArgumentException("Unsupported update operator '$operator'");
        }

        $this->operator = $operator;
        $this->pairs = $pairs;
    }

    /**
     * Get the operator.
     */
    public function getOperator(): string
    {
        return $this->operator;
    }

    /**
     * Get the field/value pairs.
     */
    public function getPairs(): array
    {
        return $this->pairs;
    }
}
