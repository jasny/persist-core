<?php

declare(strict_types=1);

namespace Jasny\DB\QueryBuilder;

use Improved\IteratorPipeline\Pipeline;

/**
 * Base class for service that can convert a filter to a database query.
 * @immutable
 */
class StagedQueryBuilder implements QueryBuilderInterface, StagesInterface
{
    protected array $stages = [
        'prepare' => [],
        'compose' => [],
        'build' => [],
        'finalize' => []
    ];


    /**
     * Return a query builder with some steps removed.
     *
     * @param callable(string,callable):bool $matcher
     * @return static
     */
    public function withoutSteps(callable $matcher): self
    {
        $clone = clone $this;

        foreach ($clone->stages as $stage => &$steps) {
            $steps = array_filter($steps, fn($step) => $matcher($stage, $step));
        }

        return $clone;
    }


    /**
     * Create a query builder, adding a custom prepare step.
     *
     * @param callable $step
     * @param bool     $replace  Replace all steps of this stage
     * @return static
     */
    final public function onPrepare(callable $step, bool $replace = false): self
    {
        return $this->withAddedStep('prepare', $step, $replace);
    }

    /**
     * Create a query builder, adding a custom compose step.
     *
     * @param callable $step
     * @param bool     $replace  Replace all steps of this stage
     * @return static
     */
    final public function onCompose(callable $step, bool $replace = false): self
    {
        return $this->withAddedStep('compose', $step, $replace);
    }

    /**
     * Create a query builder, adding a custom build step.
     *
     * @param callable $step
     * @param bool     $replace  Replace all steps of this stage
     * @return static
     */
    final public function onBuild(callable $step, bool $replace = false): self
    {
        return $this->withAddedStep('build', $step, $replace);
    }

    /**
     * Create a query builder, adding a custom finalize step.
     *
     * @param callable $step
     * @param bool     $replace  Replace all steps of this stage
     * @return static
     */
    final public function onFinalize(callable $step, bool $replace = false): self
    {
        return $this->withAddedStep('finalize', $step, $replace);
    }

    /**
     * Create a clone with a new step
     *
     * @param string   $stage
     * @param callable $step
     * @param bool     $replace
     * @return static
     */
    protected function withAddedStep(string $stage, callable $step, bool $replace = false): self
    {
        $clone = clone $this;

        if ($replace) {
            $clone->stages[$stage] = [$step];
        } else {
            $clone->stages[$stage][] = $step;
        }

        return $clone;
    }


    /**
     * Create the query from a filter
     *
     * @param iterable $filter
     * @param array    $opts
     * @return mixed
     */
    public function buildQuery(iterable $filter, array $opts = [])
    {
        return Pipeline::with($this->stages)
            ->flatten()
            ->reduce(fn($data, callable $step) => $step($data, $opts), $filter);
    }

    /**
     * Alias of `buildQuery()`
     *
     * @param iterable $filter
     * @param array    $opts
     * @return mixed
     */
    final public function __invoke(iterable $filter, array $opts = [])
    {
        return $this->buildQuery($filter, $opts);
    }
}
