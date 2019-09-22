<?php

declare(strict_types=1);

namespace Jasny\DB\QueryBuilder\Build;

use Improved\IteratorPipeline\Pipeline;
use Jasny\DB\Option\OptionInterface;

/**
 * Apply compose callbacks to query object
 */
class BuildQuery
{
    /**
     * Build a query object.
     *
     * @param object            $accumulator
     * @param iterable          $compose
     * @param OptionInterface[] $opts
     */
    public function __invoke(object $accumulator, iterable $compose, array $opts): void
    {
        Pipeline::with($compose)
            ->filter(fn($_, array $info) => isset($info['field']))
            ->apply(function (callable $callback, array $info) use ($accumulator, $opts) {
                $callback($accumulator, $info['field'], $info['operator'] ?? '', $info['value'] ?? null, $opts);
            })
            ->walk();
    }
}
