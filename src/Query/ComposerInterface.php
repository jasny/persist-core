<?php

declare(strict_types=1);

namespace Jasny\DB\Query;

use Jasny\DB\Option\OptionInterface;

/**
 * Interface for service that can apply instructions to a database specific query.
 *
 * @template TQuery
 * @template TQueryItem
 */
interface ComposerInterface
{
    /**
     * Apply instructions to given query.
     *
     * @param iterable          $items
     * @param OptionInterface[] $opts
     *
     * @phpstan-param TQuery&object               $accumulator
     * @phpstan-param iterable<TQueryItem&object> $items
     * @phpstan-param OptionInterface[]           $opts
     */
    public function compose(object $accumulator, iterable $items, array $opts = []): void;

    /**
     * Apply instructions to given query.
     *
     * @param iterable          $items
     * @param OptionInterface[] $opts
     * @return iterable
     *
     * @phpstan-param iterable<TQueryItem> $items
     * @phpstan-param OptionInterface[]    $opts
     * @phpstan-return iterable<TQueryItem>
     */
    public function prepare(iterable $items, array &$opts = []): iterable;

    /**
     * Apply items to given query.
     *
     * @param object            $accumulator  Database specific query object.
     * @param iterable          $items
     * @param OptionInterface[] $opts
     * @return iterable
     *
     * @phpstan-param TQuery&object        $accumulator
     * @phpstan-param iterable<TQueryItem> $items
     * @phpstan-param OptionInterface[]    $opts
     * @phpstan-return iterable<TQueryItem>
     */
    public function apply(object $accumulator, iterable $items, array $opts): iterable;

    /**
     * Apply instructions to given query.
     *
     * @param object            $accumulator  Database specific query object.
     * @param OptionInterface[] $opts
     *
     * @phpstan-param TQuery&object     $accumulator
     * @phpstan-param OptionInterface[] $opts
     */
    public function finalize(object $accumulator, array $opts): void;
}
