<?php

declare(strict_types=1);

namespace Jasny\DB\Query;

use Improved as i;
use Jasny\DB\Map\MapInterface;
use Jasny\DB\Map\NoMap;
use Jasny\DB\Option\Functions as opt;

/**
 * Apply the field map to items.
 *
 * @template TQuery
 * @template TItem
 * @implements ComposerInterface<TQuery,TItem>
 */
class ApplyMapToItems implements ComposerInterface
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
     * @inheritDoc
     */
    public function prepare(iterable $items, array &$opts = []): iterable
    {
        /** @var MapInterface $map */
        $map = opt\setting('map', new NoMap())->findIn($opts, MapInterface::class);

        // Quick return if there is no map
        if ($map instanceof NoMap) {
            return $items;
        }

        return i\iterable_map($items, [$map, 'apply']);
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
