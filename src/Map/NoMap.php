<?php

declare(strict_types=1);

namespace Jasny\DB\Map;

/**
 * Null object for map.
 * @immutable
 */
final class NoMap implements MapInterface
{
    /**
     * Return input.
     *
     * @template TIterable of iterable
     * @phpstan-param TIterable $iterable
     * @phpstan-return TIterable
     */
    protected function nop(iterable $iterable): iterable
    {
        return $iterable;
    }

    /**
     * @inheritDoc
     */
    public function toDB(string $field)
    {
        return $field;
    }

    /**
     * @inheritDoc
     */
    public function forFilter(): callable
    {
        return \Closure::fromCallable([$this, 'nop']);
    }

    /**
     * @inheritDoc
     */
    public function forUpdate(): callable
    {
        return \Closure::fromCallable([$this, 'nop']);
    }

    /**
     * @inheritDoc
     */
    public function forResult(): callable
    {
        return \Closure::fromCallable([$this, 'nop']);
    }

    /**
     * @inheritDoc
     */
    public function forItems(): callable
    {
        return \Closure::fromCallable([$this, 'nop']);
    }
}
