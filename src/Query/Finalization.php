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
     * @phpstan-param callable(TQuery,OptionInterface[]):void $callback
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
    public function withPriority(int $priority): self
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
        ($this->callback)($accumulator, $opts);

        return $items;
    }
}
