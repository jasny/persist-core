<?php

declare(strict_types=1);

namespace Jasny\Persist\Query;

use Jasny\Persist\Map\MapInterface;
use Jasny\Persist\Map\NoMap;
use Jasny\Persist\Option\Functions as opt;
use Jasny\Persist\Option\OptionInterface;
use Jasny\Persist\Option\SettingOption;

/**
 * Set/change map as query composer step.
 */
class SetMap implements ComposerInterface
{
    /** @phpstan-var callable(MapInterface,OptionInterface[]):MapInterface */
    protected \Closure $callback;

    /**
     * Class constructor.
     *
     * @param MapInterface|callable(MapInterface,OptionInterface[]):MapInterface $map
     */
    public function __construct($map)
    {
        $this->callback = $map instanceof MapInterface
            ? fn() => $map
            : \Closure::fromCallable($map);
    }

    /**
     * @inheritDoc
     */
    public function getPriority(): int
    {
        return 150;
    }

    /**
     * Apply the callback to each map in opts.
     *
     * @param object            $accumulator
     * @param iterable          $items
     * @param OptionInterface[] $opts
     * @return iterable
     *
     * @template TItem
     * @phpstan-param TQuery&object     $accumulator
     * @phpstan-param iterable<TItem>   $items
     * @phpstan-param OptionInterface[] $opts
     * @phpstan-return iterable<TItem>
     */
    public function compose(object $accumulator, iterable $items, array &$opts = []): iterable
    {
        $hasMap = false;

        foreach ($opts as &$opt) {
            if (
                $opt instanceof SettingOption &&
                $opt->getName() === 'map' &&
                $opt->getValue() instanceof MapInterface
            ) {
                $hasMap = true;
                $opt = opt\setting(
                    'map',
                    ($this->callback)($opt->getValue(), $opts)
                );
            }
        }

        if (!$hasMap) {
            $opts[] = opt\setting(
                'map',
                ($this->callback)(new NoMap(), $opts)
            );
        }

        return $items;
    }
}
