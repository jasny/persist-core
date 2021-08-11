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
     */
    public function applyToField(string $field): string|false|null;

    /**
     * Get function to apply mapping to items, so the data can be used by the DB
     *
     * @template TItem
     * @phpstan-param TItem&(array<string,mixed>|object) $item
     * @phpstan-return TItem&(array<string,mixed>|object)
     */
    public function apply(array|object $item): array|object;

    /**
     * Get function to apply mapping to items of query result.
     *
     * @template TItem
     * @phpstan-param TItem&(array<string,mixed>|object) $item
     * @phpstan-return TItem&(array<string,mixed>|object)
     */
    public function applyInverse(array|object $item): array|object;
}
