<?php

declare(strict_types=1);

namespace Jasny\Persist\Query;

use Jasny\Immutable;
use Jasny\Persist\Filter\FilterItem;
use Jasny\Persist\Option\OptionInterface;

/**
 * Handle custom filter when composing a query.
 *
 * @template TQuery
 * @implements ComposerInterface<TQuery,FilterItem,FilterItem>
 */
class CustomFilter implements ComposerInterface
{
    use Immutable\With;

    protected int $priority = 700;
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
     * Set a custom priority for the composer.
     *
     * @param int $priority  Priority between 500 and 999
     * @return static
     */
    public function withPriority(int $priority): static
    {
        if ($priority < 500 || $priority >= 1000) {
            throw new \InvalidArgumentException("Priority should be between 800 and 999");
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
     * Apply custom filter to given query.
     *
     * @param TQuery&object        $accumulator
     * @param iterable<FilterItem> $filter
     * @param OptionInterface[]    $opts
     * @return iterable<FilterItem>
     */
    public function compose(object $accumulator, iterable $filter, array &$opts = []): iterable
    {
        foreach ($filter as $item) {
            if ($item->getField() !== $this->field) {
                yield $item;
                continue;
            }

            ($this->callback)($accumulator, $item, $opts);
        }
    }
}
