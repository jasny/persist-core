<?php

declare(strict_types=1);

namespace Jasny\Persist\Query;

use Jasny\Persist\Option\OptionInterface;

/**
 * Interface for service that can apply instructions to a database specific query.
 *
 * @template TQuery
 * @template TIn
 * @template TOut
 */
interface ComposerInterface
{
    /**
     * Get the composer priority.
     */
    public function getPriority(): int;

    /**
     * Apply items to given query.
     *
     * @param object            $accumulator
     * @param iterable          $items
     * @param OptionInterface[] $opts
     * @return iterable
     *
     * @phpstan-param TQuery&object     $accumulator
     * @phpstan-param iterable<TIn>     $items
     * @phpstan-param OptionInterface[] $opts
     * @phpstan-return iterable<TOut>
     */
    public function compose(object $accumulator, iterable $items, array &$opts = []): iterable;
}
