<?php

declare(strict_types=1);

namespace Jasny\Persist\Query;

use Jasny\Persist\Filter\FilterItem;
use Jasny\Persist\Map\MapInterface;
use Jasny\Persist\Map\NoMap;
use Jasny\Persist\Option\Functions as opt;
use Jasny\Persist\Option\OptionInterface;

/**
 * Apply the field map to the filter items.
 *
 * @template TQuery
 * @implements ComposerInterface<TQuery,FilterItem,FilterItem>
 */
class ApplyMapToFilter implements ComposerInterface
{
    /**
     * @inheritDoc
     */
    public function getPriority(): int
    {
        return 300;
    }

    /**
     * Apply items to given query.
     *
     * @param TQuery&object        $accumulator
     * @param iterable<FilterItem> $filter
     * @param OptionInterface[]    $opts
     * @return iterable<FilterItem>
     */
    public function compose(object $accumulator, iterable $filter, array &$opts = []): iterable
    {
        /** @var MapInterface $map */
        $map = opt\setting('map', new NoMap())->findIn($opts, MapInterface::class);

        // Quick return if there is no map
        if ($map instanceof NoMap) {
            return $filter;
        }

        return $this->applyMap($map, $filter);
    }

    /**
     * Apply mapping to filter.
     *
     * @param MapInterface         $map
     * @param iterable<FilterItem> $filter
     * @return \Generator&iterable<FilterItem>
     */
    protected function applyMap(MapInterface $map, iterable $filter): \Generator
    {
        foreach ($filter as $key => $item) {
            $item = $this->map($map, $item);

            if ($item !== null) {
                yield $key => $item;
            }
        }
    }

    /**
     * Apply mapping to filter item.
     */
    protected function map(MapInterface $map, FilterItem $item): ?FilterItem
    {
        $field = $item->getField();
        $mappedField = $map->applyToField($field);

        if ($mappedField === null) {
            return $item;
        }

        return $mappedField !== false
            ? new FilterItem($mappedField, $item->getOperator(), $item->getValue())
            : null;
    }
}
