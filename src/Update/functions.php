<?php

declare(strict_types=1);

namespace Jasny\Persist\Update\Functions;

use Improved as i;
use Jasny\Persist\Update\UpdateInstruction;

/**
 * Set a field to a value.
 *
 * @param string|array<string,mixed> $fieldOrPairs
 * @param mixed                      $value          Omit when using an array
 * @return UpdateInstruction
 */
function set($fieldOrPairs, $value = null)
{
    if (func_num_args() === 1) {
        $pairs = i\type_check($fieldOrPairs, 'array');
    } else {
        $field = i\type_check($fieldOrPairs, 'string');
        $pairs = [$field => $value];
    }

    return new UpdateInstruction('set', $pairs);
}

/**
 * Unset a field.
 *
 * @param string ...$fields
 * @return UpdateInstruction
 */
function clear(string ...$fields)
{
    $pairs = array_fill_keys($fields, null);

    return new UpdateInstruction('clear', $pairs);
}

/**
 * Set a field to a value, patching an existing object.
 *
 * @param string                     $field
 * @param array<string,mixed>|object $value
 * @return UpdateInstruction
 */
function patch(string $field, $value)
{
    i\type_check($value, ['array', 'object']);

    return new UpdateInstruction('patch', [$field => $value]);
}

/**
 * Increment a field by a specific value.
 *
 * @param string    $field
 * @param int|float $value
 * @return UpdateInstruction
 */
function inc(string $field, $value = 1)
{
    i\type_check($value, ['int', 'float']);
    return new UpdateInstruction('inc', [$field => $value]);
}

/**
 * Decrement a field by a specific value.
 *
 * @param string    $field
 * @param int|float $value
 * @return UpdateInstruction
 */
function dec(string $field, $value = 1)
{
    i\type_check($value, ['int', 'float']);
    return new UpdateInstruction('inc', [$field => -1 * $value]);
}

/**
 * Multiply a field by a specific value.
 *
 * @param string    $field
 * @param int|float $value
 * @return UpdateInstruction
 */
function mul(string $field, $value)
{
    i\type_check($value, ['int', 'float']);
    return new UpdateInstruction('mul', [$field => $value]);
}

/**
 * Divide a field by a specific value.
 *
 * @param string    $field
 * @param int|float $value
 * @return UpdateInstruction
 */
function div(string $field, $value)
{
    i\type_check($value, ['int', 'float']);
    return new UpdateInstruction('div', [$field => $value]);
}

/**
 * Get modulo a field.
 *
 * @param string    $field
 * @param int|float $value
 * @return UpdateInstruction
 */
function mod(string $field, $value)
{
    i\type_check($value, ['int', 'float']);
    return new UpdateInstruction('mod', [$field => $value]);
}


/**
 * Add a value / values to an array field.
 *
 * @param string $field
 * @param mixed  ...$value
 * @return UpdateInstruction
 */
function push(string $field, ...$value)
{
    return new UpdateInstruction('push', [$field => $value]);
}

/**
 * Remove a value / values from an array field.
 *
 * @param string $field
 * @param mixed  ...$value
 * @return UpdateInstruction
 */
function pull(string $field, ...$value)
{
    return new UpdateInstruction('pull', [$field => $value]);
}
