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
     * Get wrapped map.
     */
    public function getInner(): MapInterface
    {
        return $this->inner;
    }

    /**
     * Map App field to DB field.
     * Returns null if field isn't mapped and false if field is omitted.
     *
     * @param string $field
     * @return string|false|null
     */
    public function applyToField(string $field)
    {
        return $this->inner->applyToField($field);
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
        return $this->inner->apply($item);
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
        return $this->inner->applyInverse($item);
    }
}
