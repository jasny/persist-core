<?php

declare(strict_types=1);

namespace Jasny\Persist\Query;

use Jasny\Persist\Filter\FilterItem;
use Jasny\Persist\Option\OptionInterface;

/**
 * Handle custom filter when composing a query.
 *
 * @template TQuery
 * @implements ComposerInterface<TQuery,FilterItem>
 */
class CustomFilter implements ComposerInterface
{
    protected string $field;
    protected \Closure $callback;

    /**
     * @param string                                                  $field
     * @param callable(TQuery,FilterItem,array<OptionInterface>):void $callback
     */
    public function __construct(string $field, callable $callback)
    {
        $this->field = $field;
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
     * Apply items to given query.
     *
     * @param object               $accumulator  Database specific query object.
     * @param iterable<FilterItem> $items
     * @param OptionInterface[]    $opts
     * @return iterable<FilterItem>
     */
    public function apply(object $accumulator, iterable $items, array $opts): iterable
    {
        foreach ($items as $item) {
            if ($item->getField() !== $this->field) {
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
