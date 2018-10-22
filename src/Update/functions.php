<?php

declare(strict_types=1);

namespace Jasny\DB\Update;

use function Jasny\expect_type;

/**
 * Set a field to a value
 *
 * @param string|array $field
 * @param mixed        $value
 * @return UpdateOperation
 */
function set($field, $value = null)
{
    expect_type($field, func_num_args() === 1 ? 'array' : 'string');
    return new UpdateOperation('set', $field, $value);
}

/**
 * Set a field to a value, patching an existing object.
 *
 * @param string       $field
 * @param array|object $value
 * @return UpdateOperation
 */
function patch(string $field, $value)
{
    expect_type($value, ['array', 'object']);
    return new UpdateOperation('patch', $field, $value);
}

/**
 * Increment a field by a specific value.
 *
 * @param string    $field
 * @param int|float $value
 * @return UpdateOperation
 */
function inc(string $field, $value = 1)
{
    expect_type($value, ['int', 'float']);
    return new UpdateOperation('inc', $field, $value);
}

/**
 * Decrement a field by a specific value.
 *
 * @param string    $field
 * @param int|float $value
 * @return UpdateOperation
 */
function dec(string $field, $value = 1)
{
    expect_type($value, ['int', 'float']);
    return new UpdateOperation('dec', $field, $value);
}

/**
 * Multiply a field by a specific value.
 *
 * @param string    $field
 * @param int|float $value
 * @return UpdateOperation
 */
function mul(string $field, $value)
{
    expect_type($value, ['int', 'float']);
    return new UpdateOperation('mul', $field, $value);
}

/**
 * Multiply a field by a specific value.
 *
 * @param string    $field
 * @param int|float $value
 * @return UpdateOperation
 */
function div(string $field, $value)
{
    expect_type($value, ['int', 'float']);
    return new UpdateOperation('div', $field, $value);
}

/**
 * Multiply a field by a specific value.
 *
 * @param string    $field
 * @param int|float $value
 * @return UpdateOperation
 */
function mod(string $field, $value)
{
    expect_type($value, ['int', 'float']);
    return new UpdateOperation('mod', $field, $value);
}


/**
 * Add a value to an array field.
 *
 * @param string $field
 * @param mixed  $value
 * @return UpdateOperation
 */
function add(string $field, $value)
{
    return new UpdateOperation('add', $field, $value);
}

/**
 * Remove an value from an array field.
 *
 * @param string $field
 * @param mixed  $value
 * @return UpdateOperation
 */
function rem(string $field, $value)
{
    return new UpdateOperation('rem', $field, $value);
}
