<?php

declare(strict_types=1);

namespace Jasny\DB\Map\Traits;

use Jasny\DB\Map\MapInterface;

/**
 * Trait for maps that simply proxies to another map.
 */
trait ProxyTrait
{
    protected MapInterface $inner;

    /**
     * Map App field to DB field.
     *
     * @param string $field
     * @return string|false
     */
    public function toDB(string $field)
    {
        return $this->inner->toDB($field);
    }

    /**
     * Get the map that's being proxied.
     *
     * @return MapInterface
     */
    public function getInnerMap()
    {
        return $this->inner;
    }

    /**
     * Get function to apply mapping to filter items.
     *
     * @return callable(iterable<FilterItem>):iterable<FilterItem>
     */
    public function forFilter(): callable
    {
        return $this->inner->forFilter();
    }

    /**
     * Get function to apply mapping to update operations.
     *
     * @return callable(iterable<UpdateInstruction>):iterable<UpdateInstruction>
     */
    public function forUpdate(): callable
    {
        return $this->inner->forUpdate();
    }

    /**
     * Get function to apply mapping to query result.
     *
     * @return callable(iterable):iterable
     *
     * @template TItem
     * @phpstan-return callable(iterable<TItem>):iterable<TItem>
     */
    public function forResult(): callable
    {
        return $this->inner->forResult();
    }

    /**
     * Get function to apply mapping to items, so the data can be used by the DB.
     *
     * @return callable(iterable):iterable
     *
     * @template TItem
     * @phpstan-return callable(iterable<TItem>):iterable<TItem>
     */
    public function forItems(): callable
    {
        return $this->inner->forItems();
    }
}
