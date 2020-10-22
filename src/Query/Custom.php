<?php

declare(strict_types=1);

namespace Jasny\Persist\Query;

use Jasny\Persist\Option\OptionInterface;

/**
 * Custom step when composing a query.
 *
 * @template TQuery
 * @template TIn
 * @template TOut
 * @implements ComposerInterface<TQuery,TIn,TOut>
 */
class Custom implements ComposerInterface
{
    protected int $priority;
    protected \Closure $callback;

    /**
     * @phpstan-param int                                                             $priority
     * @phpstan-param callable(TQuery,iterable<TIn>,OptionInterface[]):iterable<TOut> $callback
     */
    public function __construct(int $priority, callable $callback)
    {
        $this->priority = $priority;
        $this->callback = \Closure::fromCallable($callback);
    }

    /**
     * @inheritDoc
     */
    public function getPriority(): int
    {
        return $this->priority;
    }

    /**
     * @inheritDoc
     */
    public function compose(object $accumulator, iterable $items, array &$opts = []): iterable
    {
        return ($this->callback)($accumulator, $items, $opts);
    }
}
