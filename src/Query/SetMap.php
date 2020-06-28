<?php

declare(strict_types=1);

namespace Jasny\DB\Query;

use Improved\IteratorPipeline\Pipeline;
use Jasny\DB\Map\MapInterface;
use Jasny\DB\Map\NoMap;
use Jasny\DB\Option\Functions as opts;
use Jasny\DB\Option\OptionInterface;
use Jasny\DB\Option\SettingOption;

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
        $oldMap = opts\setting('map', new NoMap())->findIn($opts, MapInterface::class);
        $setting = opts\setting('map', ($this->callback)($oldMap, $opts));

        $opts = Pipeline::with($opts)
            ->filter(fn($opt) => !($opt instanceof SettingOption) || $opt->getName() !== 'map')
            ->toArray();
        $opts[] = $setting;

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
