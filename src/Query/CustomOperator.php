<?php

declare(strict_types=1);

namespace Persist\Query;

use Persist\Filter\FilterItem;
use Persist\Update\UpdateInstruction;

/**
 * Handle custom filter operator when composing a query.
 *
 * @template TQuery
 * @template TQueryItem
 * @implements ComposerInterface<TQuery,TQueryItem>
 */
class CustomOperator implements ComposerInterface
{
    protected string $operator;
    protected \Closure $callback;

    /**
     * @phpstan-param string                                                  $operator
     * @phpstan-param callable(TQuery,TQueryItem,array<OptionInterface>):void $callback
     */
    public function __construct(string $operator, callable $callback)
    {
        $this->operator = $operator;
        $this->callback = \Closure::fromCallable($callback);
    }

    /**
     * @inheritDoc
     */
    public function compose(object $accumulator, iterable $items, array $opts = []): void
    {
        $this->apply($accumulator, $items, $opts);
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
        foreach ($items as $item) {
            if ($item->getOperator() !== $this->operator) {
                yield $item;
                continue;
            }

            ($this->callback)($accumulator, $item, $opts);
        }
    }

    /**
     * @inheritDoc
     */
    public function finalize(object $accumulator, array $opts): void
    {
    }
}
