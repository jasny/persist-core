<?php

declare(strict_types=1);

namespace Persist\Map\Traits;

use Persist\Map\MapInterface;

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
     *
     * @param string $appField
     * @return string|false|null
     */
    public function applyToField(string $appField)
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
     * @param array|object $item
     * @return array|object
     *
     * @template TItem
     * @phpstan-param TItem&(array|object) $item
     * @phpstan-return TItem
     */
    public function apply($item)
    {
        foreach ($this->maps as $map) {
            $item = $map->apply($item);
        }

        return $item;
    }

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
    public function applyInverse($item)
    {
        foreach (array_reverse($this->maps) as $map) {
            $item = $map->applyInverse($item);
        }

        return $item;
    }
}
