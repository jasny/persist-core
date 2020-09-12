<?php

declare(strict_types=1);

namespace Persist\Option\Functions;

use Persist\Option\FieldsOption;
use Persist\Option\FlagOption;
use Persist\Option\HydrateOption;
use Persist\Option\LimitOption;
use Persist\Option\LookupOption;
use Persist\Option\SettingOption;
use Persist\Option\SortOption;

/**
 * Only return the specified fields.
 */
function fields(string ...$fields): FieldsOption
{
    return new FieldsOption($fields);
}

/**
 * Exclude the specified fields.
 */
function omit(string ...$fields): FieldsOption
{
    return new FieldsOption($fields, true /* negate */);
}

/**
 * Sort on specified fields.
 * Prepend field with `~` for descending order.
 */
function sort(string ...$fields): SortOption
{
    return new SortOption($fields);
}

/**
 * Limit the query result / limit items written.
 */
function limit(int $limit, int $offset = 0): LimitOption
{
    return new LimitOption($limit, $offset);
}

/**
 * Limit the query result for pagination.
 */
function page(int $page, int $pageSize): LimitOption
{
    return new LimitOption($pageSize, ($page - 1) * $pageSize);
}


/**
 * Hydrate a field, loading data from related collection.
 */
function hydrate(string $field): HydrateOption
{
    return new HydrateOption($field);
}

/**
 * Expand a field, loading data from related collection.
 * Uses collection name as field name, this can be changed with `as()`.
 *
 * @param string $related
 */
function lookup(string $related): LookupOption
{
    return new LookupOption($related);
}


/**
 * How to handle existing items for save query?
 *
 * - conflict - results in db error
 * - ignore   - skip existing
 * - replace  - replace existing, missing fields are removed or set to default value
 * - update   - update existing, missing fields are not changed
 *
 * @throws \UnexpectedValueException for unknown resolution
 */
function existing(string $resolution): SettingOption
{
    if (!in_array($resolution, ['conflict', 'ignore', 'replace', 'update'], true)) {
        throw new \UnexpectedValueException("Unsupported conflict resolution option '$resolution'");
    }

    return new SettingOption('existing', $resolution);
}

/**
 * Generic flag for a query.
 */
function flag(string $name): FlagOption
{
    return new FlagOption($name);
}

/**
 * Generic setting for a query.
 *
 * @param string $name
 * @param mixed  $value
 * @return SettingOption
 */
function setting(string $name, $value): SettingOption
{
    return new SettingOption($name, $value);
}
