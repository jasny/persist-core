<?php

declare(strict_types=1);

namespace Jasny\Persist\Map\Traits;

use Jasny\Persist\Map\MapInterface;

/**
 * Trait for maps that simply combine other maps.
 */
trait CombineTrait
{
    /** @var MapInterface[] $maps */
    protected array $maps = [];

    /**
     * Get wrapped maps.
     *
     * @return MapInterface[]
     */
    public function getInner(): array
    {
        return $this->maps;
    }

    /**
     * Map App field to DB field.
     * Returns null if field isn't mapped and false if field is omitted.
     */
    public function applyToField(string $appField): string|false|null
    {
        $field = $appField;

        foreach ($this->maps as $map) {
            $field = $map->applyToField($field) ?? $field;

            if ($field === false) {
                break;
            }
        }

        return $field !== $appField ? $field : null;
    }

    /**
     * Get function to apply mapping to items, so the data can be used by the DB.
     *
     * @template TItem
     * @phpstan-param TItem&(array<string,mixed>|object) $item
     * @phpstan-return TItem&(array<string,mixed>|object)
     */
    public function apply(array|object $item): array|object
    {
        foreach ($this->maps as $map) {
            $item = $map->apply($item);
        }

        return $item;
    }

    /**
     * Get function to apply mapping to items of query result.
     *
     * @template TItem
     * @phpstan-param TItem&(array<string,mixed>|object) $item
     * @phpstan-return TItem&(array<string,mixed>|object)
     */
    public function applyInverse(array|object $item): array|object
    {
        foreach (array_reverse($this->maps) as $map) {
            $item = $map->applyInverse($item);
        }

        return $item;
    }
}
