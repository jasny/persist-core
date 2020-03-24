<?php

declare(strict_types=1);

namespace Jasny\DB\Result;

use Improved\IteratorPipeline\PipelineBuilder;
use Jasny\DB\Map\MapInterface;
use Jasny\DB\Map\NoMap;
use Jasny\DB\Option\Functions as opts;
use Jasny\DB\Option\OptionInterface;

/**
 * Pipeline builder for a query result.
 * @immutable
 *
 * @template TValue
 */
class ResultBuilder extends PipelineBuilder
{
    /**
     * Apply query options, like mapping, to result.
     *
     * @param OptionInterface[] $opts
     * @return static
     */
    public function withOpts(array $opts): self
    {
        /** @var MapInterface|null $map */
        $map = opts\setting('map', new NoMap())->findIn($opts, MapInterface::class);

        return !($map instanceof NoMap)
            ? $this->map([$map, 'applyInverse'])
            : $this;
    }

    /**
     * Create a result.
     *
     * @phpstan-param iterable<TValue>    $iterable
     * @phpstan-param array<string,mixed> $meta
     * @phpstan-return Result<TValue>
     */
    public function with(iterable $iterable, array $meta = []): Result
    {
        $result = new Result($iterable, $meta);

        foreach ($this->steps as [$callback, $args]) {
            $result->then($callback, ...$args);
        }

        return $result;
    }
}
