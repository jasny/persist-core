<?php

declare(strict_types=1);

namespace Jasny\Persist\Query;

use Improved as i;
use Jasny\Persist\Option\OptionInterface;

/**
 * Compound class for query composers.
 *
 * @template TQuery
 * @template TQueryItem
 * @implements ComposerInterface<TQuery,TQueryItem>
 */
class Composer implements ComposerInterface
{
    /**
     * @phpstan-var array<ComposerInterface<TQuery,TQueryItem>>
     */
    public array $steps;

    /**
     * @phpstan-param ComposerInterface<TQuery,TQueryItem> ...$steps
     */
    public function __construct(ComposerInterface ...$steps)
    {
        $this->steps = $steps;
    }

    /**
     * Apply instructions to given query.
     *
     * @param iterable          $items
     * @param OptionInterface[] $opts
     *
     * @phpstan-param TQuery&object        $accumulator
     * @phpstan-param iterable<TQueryItem> $items
     * @phpstan-param OptionInterface[]    $opts
     */
    public function compose(object $accumulator, iterable $items, array $opts = []): void
    {
        $items = $this->prepare($items, $opts);

        i\iterable_walk(
            $this->apply($accumulator, $items, $opts)
        );

        $this->finalize($accumulator, $opts);
    }

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
    public function prepare(iterable $items, array &$opts = []): iterable
    {
        foreach ($this->steps as $step) {
            $items = $step->prepare($items, $opts);
        }

        return $items;
    }

    /**
     * Apply items to given query.
     *
     * @param object            $accumulator  Database specific query object.
     * @param iterable          $items
     * @param OptionInterface[] $opts
     * @return array
     *
     * @phpstan-param TQuery&object        $accumulator
     * @phpstan-param iterable<TQueryItem> $items
     * @phpstan-param OptionInterface[]    $opts
     * @phpstan-return array<TQueryItem>
     */
    public function apply(object $accumulator, iterable $items, array $opts): iterable
    {
        foreach ($this->steps as $step) {
            $items = $step->apply($accumulator, $items, $opts);
        }

        return $items;
    }

    /**
     * Apply instructions to given query.
     *
     * @param object            $accumulator  Database specific query object.
     * @param OptionInterface[] $opts
     *
     * @phpstan-param TQuery&object     $accumulator
     * @phpstan-param OptionInterface[] $opts
     */
    public function finalize(object $accumulator, array $opts): void
    {
        foreach ($this->steps as $step) {
            $step->finalize($accumulator, $opts);
        }
    }
}
