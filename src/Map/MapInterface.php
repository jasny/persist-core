<?php

declare(strict_types=1);

namespace Jasny\Persist\Map;

use Jasny\Persist\Option\OptionInterface;

/**
 * Map DB fields to App fields and visa versa.
 */
interface MapInterface
{
    /**
     * Apply options to map.
     *
     * @param OptionInterface[] $opts
     * @return MapInterface
     */
    public function withOpts(array $opts): MapInterface;

    /**
     * Map App field to DB field.
     * Returns null if field isn't mapped and false if field is omitted.
     *
     * @param string $field
     * @return string|false|null
     */
    public function applyToField(string $field);

    /**
     * Get function to apply mapping to items, so the data can be used by the DB
     *
     * @param array|object $item
     * @return array|object
     *
     * @template TItem
     * @phpstan-param TItem&(array|object) $item
     * @phpstan-return TItem
     */
    public function apply($item);

    /**
     * Get function to apply mapping to items of query result.
     *
     * @param array|object $item
     * @return array|object
     *
     * @template TItem
     * @phpstan-param TItem&(array|object) $item
     * @phpstan-return TItem
     */
    public function applyInverse($item);
}
