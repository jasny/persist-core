<?php

declare(strict_types=1);

namespace Jasny\DB\QueryBuilder\Save\Prepare;

use Jasny\DB\Map\MapInterface;
use Jasny\DB\Map\NoMap;
use Jasny\DB\Option as opts;
use Jasny\DB\Option\OptionInterface;

/**
 * Apply the field map to items.
 */
class MapItems
{
    /**
     * Invoke the map on items, so the can be used in the db.
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
        $map = opts\setting('map', new NoMap());

        foreach ($items as $item) {
            yield $map->apply($item);
        }
    }
}
