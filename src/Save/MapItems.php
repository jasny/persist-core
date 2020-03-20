<?php

declare(strict_types=1);

namespace Jasny\DB\Save;

use Jasny\DB\Map\MapInterface;
use Jasny\DB\Map\NoMap;
use Jasny\DB\Option\Functions as opts;
use Jasny\DB\Option\OptionInterface;

/**
 * Apply the field map to items.
 */
class MapItems
{
    /**
     * Apply the map to items, so they can be used in the db.
     *
     * @param iterable          $items
     * @param OptionInterface[] $opts
     * @return iterable
     *
     * @template TItem
     * @phpstan-param iterable<TItem>   $items
     * @phpstan-param OptionInterface[] $opts
     * @phpstan-return iterable<TItem>
     */
    public function __invoke(iterable $items, array $opts): iterable
    {
        /** @var MapInterface $map */
        $map = opts\setting('map', null)->findIn($opts);

        return $map !== null && !($map instanceof NoMap)
            ? $this->apply($map, $items)
            : $items;
    }

    /**
     * @param MapInterface $map
     * @param iterable     $items
     * @return \Generator
     *
     * @template TItem
     * @phpstan-param MapInterface      $map
     * @phpstan-param iterable<TItem>   $items
     * @phpstan-return \Generator&iterable<TItem>
     */
    protected function apply(MapInterface $map, iterable $items): \Generator
    {
        foreach ($items as $item) {
            yield $map->apply($item);
        }
    }
}
