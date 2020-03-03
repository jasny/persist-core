<?php

declare(strict_types=1);

namespace Jasny\DB\Filter;

use Improved\IteratorPipeline\Pipeline;
use Jasny\DB\Map\MapInterface;
use Jasny\DB\Map\NoMap;
use Jasny\DB\Option\Functions as opts;
use Jasny\DB\Option\OptionInterface;

/**
 * Apply the field map to the filter items.
 */
class MapFilter
{
    /**
     * Invoke the map.
     *
     * @param FilterItem[] $filterItems
     * @param OptionInterface[] $opts
     * @return FilterItem[]
     */
    public function __invoke(array $filterItems, array $opts): array
    {
        /** @var MapInterface|null $map */
        $map = opts\setting('map', null);

        // Quick return if there is no map
        if ($map === null || $map instanceof NoMap) {
            return $filterItems;
        }

        return Pipeline::with($filterItems)
            ->map(fn($filterItem) => $this->apply($map, $filterItem))
            ->toArray();
    }

    /**
     * Apply mapping to filter item.
     */
    protected function apply(MapInterface $map, FilterItem $item): FilterItem
    {
        $field = $item->getField();
        $mappedField = $map->applyToField($field);

        return $mappedField !== null && $mappedField !== false
            ? new FilterItem($mappedField, $item->getOperator(), $item->getValue())
            : $item;
    }
}
