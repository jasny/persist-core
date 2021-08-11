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
function set(string|array $fieldOrPairs, mixed $value = null): UpdateInstruction
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
 */
function clear(string ...$fields): UpdateInstruction
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
function patch(string $field, array|object $value): UpdateInstruction
{
    return new UpdateInstruction('patch', [$field => $value]);
}

/**
 * Increment a field by a specific value.
 */
function inc(string $field, int|float $value = 1): UpdateInstruction
{
    return new UpdateInstruction('inc', [$field => $value]);
}

/**
 * Decrement a field by a specific value.
 */
function dec(string $field, int|float $value = 1): UpdateInstruction
{
    return new UpdateInstruction('inc', [$field => -1 * $value]);
}

/**
 * Multiply a field by a specific value.
 */
function mul(string $field, int|float $value): UpdateInstruction
{
    return new UpdateInstruction('mul', [$field => $value]);
}

/**
 * Divide a field by a specific value.
 */
function div(string $field, int|float $value): UpdateInstruction
{
    return new UpdateInstruction('div', [$field => $value]);
}

/**
 * Get modulo a field.
 */
function mod(string $field, int|float $value): UpdateInstruction
{
    return new UpdateInstruction('mod', [$field => $value]);
}


/**
 * Add a value / values to an array field.
 */
function push(string $field, mixed ...$value): UpdateInstruction
{
    return new UpdateInstruction('push', [$field => $value]);
}

/**
 * Remove a value / values from an array field.
 */
function pull(string $field, mixed ...$value): UpdateInstruction
{
    return new UpdateInstruction('pull', [$field => $value]);
}
