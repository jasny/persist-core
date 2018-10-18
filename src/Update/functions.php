<?php

declare(strict_types=1);

namespace Jasny\DB\Update;

use Improved\IteratorPipeline\Pipeline;
use function Jasny\expect_type;


/** @internal */
const set = 'Jasny\DB\Update\set';
/** @internal */
const patch = 'Jasny\DB\Update\patch';
/** @internal */
const inc = 'Jasny\DB\Update\inc';
/** @internal */
const dec = 'Jasny\DB\Update\dec';
/** @internal */
const mul = 'Jasny\DB\Update\mul';
/** @internal */
const div = 'Jasny\DB\Update\div';
/** @internal */
const mod = 'Jasny\DB\Update\mod';
/** @internal */
const add = 'Jasny\DB\Update\add';
/** @internal */
const rem = 'Jasny\DB\Update\rem';


/**
 * Set a field to a value
 *
 * @param string|iterable $field
 * @param mixed           $value
 * @return UpdateOperation
 */
function set($field, $value = null)
{
    expect_type($field, ['string', 'iterable']);
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
    return new UpdateOperation('set', $field, $value);
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
 * Multiply a field by a specific value.
 *
 * @param string    $field
 * @param int|float $value
 * @return UpdateOperation
 */
function add(string $field, $value)
{
    expect_type($value, ['int', 'float']);
    return new UpdateOperation('add', $field, $value);
}

/**
 * Multiply a field by a specific value.
 *
 * @param string    $field
 * @param int|float $value
 * @return UpdateOperation
 */
function rem(string $field, $value)
{
    expect_type($value, ['int', 'float']);
    return new UpdateOperation('rem', $field, $value);
}
