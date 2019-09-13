<?php

declare(strict_types=1);

namespace Jasny\DB\Update;

use Improved as i;

/**
 * Set a field to a value
 *
 * @param string|array $fieldOrPairs
 * @param mixed        $value
 * @return UpdateOperation
 */
function set($fieldOrPairs, $value = null)
{
    if (func_num_args() === 1) {
        $pairs = i\type_check($fieldOrPairs, 'array');
    } else {
        $field = i\type_check($fieldOrPairs, 'string');
        $pairs = [$field => $value];
    }

    return new UpdateOperation('set', $pairs);
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
    i\type_check($value, ['array', 'object']);

    return new UpdateOperation('patch', [$field => $value]);
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
    i\type_check($value, ['int', 'float']);
    return new UpdateOperation('inc', [$field => $value]);
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
    i\type_check($value, ['int', 'float']);
    return new UpdateOperation('inc', [$field => -1 * $value]);
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
    i\type_check($value, ['int', 'float']);
    return new UpdateOperation('mul', [$field => $value]);
}

/**
 * Divide a field by a specific value.
 *
 * @param string    $field
 * @param int|float $value
 * @return UpdateOperation
 */
function div(string $field, $value)
{
    i\type_check($value, ['int', 'float']);
    return new UpdateOperation('div', [$field => $value]);
}

/**
 * Get modulo a field.
 *
 * @param string    $field
 * @param int|float $value
 * @return UpdateOperation
 */
function mod(string $field, $value)
{
    i\type_check($value, ['int', 'float']);
    return new UpdateOperation('mod', [$field => $value]);
}


/**
 * Add a value / values to an array field.
 *
 * @param string $field
 * @param mixed  ...$value
 * @return UpdateOperation
 */
function push(string $field, ...$value)
{
    return new UpdateOperation('push', [$field => $value]);
}

/**
 * Remove a value / values from an array field.
 *
 * @param string $field
 * @param mixed  ...$value
 * @return UpdateOperation
 */
function pull(string $field, ...$value)
{
    return new UpdateOperation('pull', [$field => $value]);
}
