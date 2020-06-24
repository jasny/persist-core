<?php

declare(strict_types=1);

namespace Jasny\DB\QueryComposer;

use Jasny\DB\Option\OptionInterface;

/**
 * Custom preparation step when composing a query.
 *
 * @template TQueryItem
 * @implements ComposerInterface<object,TQueryItem>
 */
class Preparation implements ComposerInterface
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
     * @throws \LogicException
     */
    public function compose(object $accumulator, iterable $items, array $opts = []): void
    {
        throw new \LogicException(__CLASS__ . ' can only be used in combination with other query composers');
    }

    /**
     * @inheritDoc
     */
    public function prepare(iterable $items, array &$opts = []): iterable
    {
        return ($this->callback)($items, $opts);
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
    }
}
