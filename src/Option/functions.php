<?php declare(strict_types=1);

namespace Jasny\DB\Option;

/**
 * Only return the specified fields.
 *
 * @param string ...$fields
 * @return FieldsOption
 */
function fields(string ...$fields): FieldsOption
{
    return new FieldsOption('fields', $fields);
}

/**
 * Exclude the specified fields.
 *
 * @param string ...$fields
 * @return FieldsOption
 */
function omit(string ...$fields): FieldsOption
{
    return new FieldsOption('omit', $fields);
}

/**
 * Sort on specified fields.
 * Prepend field with `~` for descending order.
 *
 * @param string ...$fields
 * @return FieldsOption
 */
function sort(string ...$fields): FieldsOption
{
    return new FieldsOption('sort', $fields);
}

/**
 * Limit the query result.
 *
 * @param int $limit
 * @param int $offset
 * @return LimitOption
 */
function limit(int $limit, int $offset = 0): LimitOption
{
    return new LimitOption($limit, $offset);
}

/**
 * Limit the query result for pagination.
 *
 * @param int $page
 * @param int $pageSize
 * @return LimitOption
 */
function page(int $page, int $pageSize): LimitOption
{
    return new LimitOption($pageSize, ($page - 1) * $pageSize);
}
