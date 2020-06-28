<?php

declare(strict_types=1);

namespace Jasny\DB\Query;

use Improved\IteratorPipeline\Pipeline;
use Jasny\DB\Filter\FilterItem;
use Jasny\DB\Map\MapInterface;
use Jasny\DB\Map\NoMap;
use Jasny\DB\Option\Functions as opts;
use Jasny\DB\Option\OptionInterface;

/**
 * Apply the field map to the filter items.
 *
 * @template TQuery
 * @implements ComposerInterface<TQuery,FilterItem>
 */
class ApplyMapToFilter implements ComposerInterface
{
    /**
     * @inheritDoc
     * @throws \LogicException
     */
    public function compose(object $accumulator, iterable $items, array $opts = []): void
    {
        throw new \LogicException(__CLASS__ . ' can only be used in combination with other query composers');
    }

    /**
     * Invoke the parser.
     *
     * @param iterable<FilterItem> $filter
     * @param OptionInterface[]    $opts
     * @return iterable<FilterItem>
     */
    public function prepare(iterable $filter, array &$opts = []): iterable
    {
        /** @var MapInterface $map */
        $map = opts\setting('map', new NoMap())->findIn($opts, MapInterface::class);

        // Quick return if there is no map
        if ($map instanceof NoMap) {
            return $filter;
        }

        return Pipeline::with($filter)
            ->map(fn(FilterItem $item) => $this->map($map, $item))
            ->cleanup();
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

    /**
     * @inheritDoc
     */
    public function apply(object $accumulator, iterable $items, array $opts): iterable
    {
        return $items;
    }

    /**
     * @inheritDoc
     */
    public function finalize(object $accumulator, array $opts): void
    {
    }
}
