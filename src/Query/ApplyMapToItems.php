<?php

declare(strict_types=1);

namespace Persist\Query;

use Improved as i;
use Persist\Map\MapInterface;
use Persist\Map\NoMap;
use Persist\Option\Functions as opt;
use Persist\Option\OptionInterface;

/**
 * Apply the field map to items.
 *
 * @template TQuery
 * @template TItem
 * @implements ComposerInterface<TQuery,TItem,TItem>
 */
class ApplyMapToItems implements ComposerInterface
{
    /**
     * Get the composer priority.
     */
    public function getPriority(): int
    {
        return 300;
    }

    /**
     * Apply items to given query.
     *
     * @param object            $accumulator
     * @param iterable          $items
     * @param OptionInterface[] $opts
     * @return iterable
     *
     * @phpstan-param TQuery&object     $accumulator
     * @phpstan-param iterable<TItem>   $items
     * @phpstan-param OptionInterface[] $opts
     * @phpstan-return iterable<TItem>
     */
    public function compose(object $accumulator, iterable $items, array &$opts = []): iterable
    {
        /** @var MapInterface $map */
        $map = opt\setting('map', new NoMap())->findIn($opts, MapInterface::class);

        // Quick return if there is no map
        if ($map instanceof NoMap) {
            return $items;
        }

        return i\iterable_map($items, [$map, 'apply']);
    }
}
