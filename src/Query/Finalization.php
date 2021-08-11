<?php

declare(strict_types=1);

namespace Jasny\Persist\Query;

use Jasny\Immutable;
use Jasny\Persist\Option\OptionInterface;

/**
 * Custom finalization step when composing a query.
 *
 * @template TQuery
 * @implements ComposerInterface<TQuery,mixed,mixed>
 */
class Finalization implements ComposerInterface
{
    use Immutable\With;

    protected int $priority = 1200;
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
     * @param int $priority  Priority between 500 and 999
     * @return static
     */
    public function withPriority(int $priority): static
    {
        if ($priority <= 1000 || $priority >= 2000) {
            throw new \InvalidArgumentException("Priority should be between 1001 and 1999");
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
