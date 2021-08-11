<?php

declare(strict_types=1);

namespace Jasny\Persist\Query;

use Jasny\Immutable;
use Jasny\Persist\Filter\FilterItem;
use Jasny\Persist\Option\OptionInterface;
use Jasny\Persist\Update\UpdateInstruction;

/**
 * Handle custom filter operator when composing a query.
 *
 * @template TQuery
 * @template TQueryItem of FilterItem|UpdateInstruction
 * @implements ComposerInterface<TQuery,TQueryItem,TQueryItem>
 */
class CustomOperator implements ComposerInterface
{
    use Immutable\With;

    protected int $priority = 800;
    protected string $operator;
    protected \Closure $callback;

    /**
     * @param string                                                  $operator
     * @param callable(TQuery,TQueryItem,array<OptionInterface>):void $callback
     */
    public function __construct(string $operator, callable $callback)
    {
        $this->operator = $operator;
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
     * Apply items to given query.
     *
     * @param TQuery&object        $accumulator
     * @param iterable<TQueryItem> $items
     * @param OptionInterface[]    $opts
     * @return iterable<TQueryItem>
     */
    public function compose(object $accumulator, iterable $items, array &$opts = []): iterable
    {
        foreach ($items as $item) {
            if ($item->getOperator() !== $this->operator) {
                yield $item;
                continue;
            }

            ($this->callback)($accumulator, $item, $opts);
        }
    }
}
