<?php

declare(strict_types=1);

namespace Persist\Query;

use Persist\Option\OptionInterface;

/**
 * Custom finalization step when composing a query.
 *
 * @template TQuery
 * @implements ComposerInterface<TQuery,mixed>
 */
class Finalization implements ComposerInterface
{
    protected \Closure $callback;

    /**
     * @phpstan-param callable(TQuery,OptionInterface[]):void $callback
     */
    public function __construct(callable $callback)
    {
        $this->callback = \Closure::fromCallable($callback);
    }

    /**
     * @inheritDoc
     */
    public function compose(object $accumulator, iterable $items, array $opts = []): void
    {
        $this->finalize($accumulator, $opts);
    }

    /**
     * @inheritDoc
     */
    public function prepare(iterable $items, array &$opts = []): iterable
    {
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
        ($this->callback)($accumulator, $opts);
    }
}
