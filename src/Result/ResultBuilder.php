<?php

declare(strict_types=1);

namespace Jasny\Persist\Result;

use Improved\IteratorPipeline\PipelineBuilder;
use Jasny\Persist\Map\MapInterface;
use Jasny\Persist\Map\NoMap;
use Jasny\Persist\Option\Functions as opt;
use Jasny\Persist\Option\OptionInterface;

/**
 * Pipeline builder for a query result.
 *
 * @template TValue
 */
class ResultBuilder extends PipelineBuilder
{
    protected string $class;

    /**
     * Class constructor.
     */
    public function __construct(string $class = Result::class)
    {
        if (!is_a($class, Result::class, true)) {
            throw new \LogicException("$class doesn't extend " . Result::class);
        }

        $this->class = $class;
    }

    /**
     * Apply query options, like mapping, to result.
     *
     * @param OptionInterface[] $opts
     * @return static
     */
    public function withOpts(array $opts): self
    {
        /** @var MapInterface $map */
        $map = opt\setting('map', new NoMap())->findIn($opts, MapInterface::class);

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
        $class = $this->class;

        /** @var Result $result */
        $result = new $class($iterable, $meta);

        foreach ($this->steps as [$callback, $args]) {
            $result->then($callback, ...$args);
        }

        return $result;
    }
}
