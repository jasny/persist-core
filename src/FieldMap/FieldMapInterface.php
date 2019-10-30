<?php

declare(strict_types=1);

namespace Jasny\DB\FieldMap;

use Jasny\DB\Filter\FilterItem;
use Jasny\DB\Update\UpdateInstruction;

/**
 * Map DB field to PHP field or visa versa.
 */
interface FieldMapInterface
{
    /**
     * Map an app field name to a DB field name.
     * Field name use dot notation.
     */
    public function toDB(string $appField): string;

    /**
     * Map a DB field name to an app field name.
     */
    public function fromDB(string $dbField): string;


    /**
     * Apply mapping to filter items.
     *
     * @param FilterItem[] $filterItems
     * @return FilterItem[]
     */
    public function applyToFilter(array $filterItems): array;

    /**
     * Apply mapping to update operations.
     *
     * @param UpdateInstruction[] $update
     * @return UpdateInstruction[]
     */
    public function applyToUpdate(array $update): array;

    /**
     * Apply mapping to query result.
     *
     * @param iterable<array|object> $result
     * @return iterable<array|object>
     */
    public function applyToResult(iterable $result): iterable;

    /**
     * Apply inverted mapping to items, so the data can be used by the DB.
     *
     * @param iterable<array|object> $items
     * @return iterable<array|object>
     */
    public function applyToItems(iterable $items): iterable;
}
