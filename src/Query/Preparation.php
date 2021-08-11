<?php

declare(strict_types=1);

namespace Jasny\Persist\Query;

use Jasny\Immutable;
use Jasny\Persist\Option\OptionInterface;

/**
 * Custom preparation step when composing a query.
 *
 * @template TQuery
 * @implements ComposerInterface<TQuery,mixed,mixed>
 */
class Preparation implements ComposerInterface
{
    use Immutable\With;

    protected int $priority = 200;
    protected \Closure $callback;

    /**
     * @param callable(TQuery,OptionInterface[]):void $callback
     */
    public function __construct(callable $callback)
    {
        $this->callback = \Closure::fromCallable($callback);
    }

    /**
     * Set a custom priority for the composer.
     *
     * @param int $priority  Priority between 100 and 499
     * @return static
     */
    public function withPriority(int $priority): static
    {
        if ($priority < 100 || $priority >= 500) {
            throw new \InvalidArgumentException("Priority should be between 100 and 499");
        }

        return $this->withProperty('priority', $priority);
    }

    /**
     * @inheritDoc
     */
    public function getPriority(): int
    {
        return $this->priority;
    }

    /**
     * Apply step to given query.
     *
     * @template TItem
     * @param TQuery&object     $accumulator
     * @param iterable<TItem>   $items
     * @param OptionInterface[] $opts
     * @return iterable<TItem>
     */
    public function compose(object $accumulator, iterable $items, array &$opts = []): iterable
    {
        ($this->callback)($accumulator, $opts);

        return $items;
    }
}
