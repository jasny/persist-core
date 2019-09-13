<?php

declare(strict_types=1);

namespace Jasny\DB\Option;

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
 * Limit the query result.
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
