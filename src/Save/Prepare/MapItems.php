<?php

declare(strict_types=1);

namespace Jasny\DB\Save\Prepare;

use Improved as i;
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
        /** @var MapInterface|null $map */
        $map = opts\setting('map', null)->findIn($opts, MapInterface::class);

        // Quick return if there is no map
        if ($map === null || $map instanceof NoMap) {
            return $items;
        }

        return i\iterable_map($items, [$map, 'apply']);
    }
}
