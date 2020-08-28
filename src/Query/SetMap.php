<?php

declare(strict_types=1);

namespace Jasny\Persist\Query;

use Jasny\Persist\Map\MapInterface;
use Jasny\Persist\Map\NoMap;
use Jasny\Persist\Option\Functions as opt;
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
    public function compose(object $accumulator, iterable $items, array $opts = []): void
    {
        throw new \LogicException(__CLASS__ . ' can only be used in combination with other query composers');
    }

    /**
     * @inheritDoc
     */
    public function prepare(iterable $items, array &$opts = []): iterable
    {
        $map = null;

        foreach ($opts as &$opt) {
            if (
                $opt instanceof SettingOption &&
                $opt->getName() === 'map' &&
                $opt->getValue() instanceof MapInterface
            ) {
                $map = ($this->callback)($opt->getValue(), $opts);
                $opt = opt\setting('map', $map);
            }
        }

        if ($map === null) {
            $map = ($this->callback)(new NoMap(), $opts);
            $opts[] = opt\setting('map', $map);
        }

        return $items;
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
