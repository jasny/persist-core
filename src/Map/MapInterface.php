<?php

declare(strict_types=1);

namespace Jasny\DB\Map;

use Jasny\DB\Filter\FilterItem;
use Jasny\DB\Update\UpdateInstruction;

/**
 * Map DB fields to App fields and visa versa.
 */
interface MapInterface
{
    /**
     * Map App field to DB field.
     *
     * @param string $field
     * @return string|false
     */
    public function toDB(string $field);

    /**
     * Get function to apply mapping to filter items.
     *
     * @return callable(iterable<FilterItem>):iterable<FilterItem>
     */
    public function forFilter(): callable;

    /**
     * Get function to apply mapping to update operations.
     *
     * @return callable(iterable<UpdateInstruction>):iterable<UpdateInstruction>
     */
    public function forUpdate(): callable;

    /**
     * Get function to apply mapping to query result.
     *
     * @return callable(iterable):iterable
     *
     * @template TItem
     * @phpstan-return callable(iterable<TItem>):iterable<TItem>
     */
    public function forResult(): callable;

    /**
     * Get function to apply mapping to items, so the data can be used by the DB.
     *
     * @return callable(iterable):iterable
     *
     * @template TItem
     * @phpstan-return callable(iterable<TItem>):iterable<TItem>
     */
    public function forItems(): callable;
}
