<?php

declare(strict_types=1);

namespace Jasny\DB\QueryBuilder;

use Improved as i;
use Jasny\DB\Exception\BuildQueryException;
use Jasny\DB\Option\OptionInterface;

/**
 * Query builder with customizable stages. The 3 stages are
 *
 * - prepare -> parse filters, mapping, casting
 *   The prepare steps are callables that takes an iterable and must return an iterable.
 *
 * - compose -> create filter function per field
 *   The compose steps are callables that takes an iterable and must return an iterable with callables.
 *
 * - build -> reduce into a database query
 *   The build steps takes an iterable with callables and applies them, creating a database specific query object. The
 *   required arguments is db implementation specific.
 *
 * The steps of all stages are called consecutively as pipeline; the return value is passed as first argument of the
 * next step. The second argument is always the set of options.
 *
 * @immutable
 */
class StagedQueryBuilder implements QueryBuilderInterface
{
    /**
     * @var array<int,callable(iterable,array):iterable>
     */
    protected array $prepareSteps = [];

    /**
     * @var array<int,callable(iterable,array):iterable>
     */
    protected array $composeSteps = [];

    /**
     * @var array<int,callable>
     */
    protected array $buildSteps = [];


    /**
     * Return a query builder with an additional prepare step.
     *
     * @param callable(iterable):iterable $step
     * @return static
     */
    public function onPrepare(callable $step): self
    {
        return $this->withAddedStep('prepare', $step);
    }

    /**
     * Return a query builder with some or all prepare steps removed.
     *
     * @param null|callable(string,callable):bool $matcher  Return `true` to remove the step
     * @return static
     */
    public function withoutPrepare(?callable $matcher = null): self
    {
        return $this->withoutSteps('prepare', $matcher);
    }


    /**
     * Return a query builder with an additional compose step.
     *
     * @param callable(iterable):iterable $step
     * @return static
     */
    public function onCompose(callable $step): self
    {
        return $this->withAddedStep('compose', $step);
    }

    /**
     * Return a query builder with some or all compose steps removed.
     *
     * @param null|callable(string,callable):bool $matcher  Return `true` to remove the step.
     * @return static
     */
    public function withoutCompose(?callable $matcher = null): self
    {
        return $this->withoutSteps('compose', $matcher);
    }

    /**
     * Return a query builder with an additional build step.
     *
     * @param callable $step
     * @return static
     */
    public function onBuild(callable $step): self
    {
        return $this->withAddedStep('build', $step);
    }

    /**
     * Return a query builder with some or all compose steps removed.
     *
     * @param null|callable(string,callable):bool $matcher  Return `true` to remove the step.
     * @return static
     */
    public function withoutBuild(?callable $matcher = null): self
    {
        return $this->withoutSteps('build', $matcher);
    }


    /**
     * Add steps to a stage.
     */
    private function withAddedStep(string $stage, callable $step): self
    {
        $prop = $stage . 'Steps';

        $clone = clone $this;
        $clone->{$prop}[] = $step;

        return $clone;
    }

    /**
     * Remove steps from a stage.
     */
    private function withoutSteps(string $stage, ?callable $matcher = null): self
    {
        $prop = $stage . 'Steps';

        $steps = $matcher !== null ? array_filter($this->{$prop}, fn($step) => !$matcher($step)) : [];

        if ($this->{$prop} === $steps) {
            return $this;
        }

        $clone = clone $this;
        $clone->{$prop} = $steps;

        return $clone;
    }


    /**
     * Create the query from a filter.
     *
     * @param iterable          $filter
     * @param OptionInterface[] $opts
     * @return mixed
     * @throws BuildQueryException
     */
    public function buildQuery(iterable $filter, array $opts = [])
    {
        $prepared = $this->prepare($filter, $opts);
        $compose = $this->compose($prepared, $opts);
        $query = $this->build($compose, $opts);

        return $query;
    }

    /**
     * Alias of `buildQuery()`.
     *
     * @param iterable          $filter
     * @param OptionInterface[] $opts
     * @return mixed
     * @throws BuildQueryException
     */
    final public function __invoke(iterable $filter, array $opts = [])
    {
        return $this->buildQuery($filter, $opts);
    }

    /**
     * Parse filters and apply mapping.
     *
     * @param iterable          $payload
     * @param OptionInterface[] $opts
     * @return iterable
     * @throws BuildQueryException
     */
    protected function prepare(iterable $payload, array $opts = []): iterable
    {
        if ($this->prepareSteps === []) {
            throw new \LogicException("Unusable query builder; no prepare step");
        }

        $unexpectedException = new \UnexpectedValueException('Expected %2$s, %1$s returned');

        foreach ($this->prepareSteps as $i => $step) {
            try {
                $payload = i\type_check($step($payload, $opts), 'iterable', $unexpectedException);
            } catch (\Throwable $exception) {
                throw $this->buildException('prepare', $i, $exception);
            }
        }

        return $payload;
    }

    /**
     * Compose callbacks to apply filter in build step.
     *
     * @param iterable          $payload
     * @param OptionInterface[] $opts
     * @return iterable<callable>
     * @throws BuildQueryException
     */
    protected function compose(iterable $payload, array $opts = []): iterable
    {
        if ($this->composeSteps === []) {
            throw new \LogicException("Unusable query builder; no compose step");
        }

        $unexpectedException = new \UnexpectedValueException('Expected %2$s, %1$s returned');

        foreach ($this->composeSteps as $i => $step) {
            try {
                $iterator = i\type_check($step($payload, $opts), 'iterable', $unexpectedException);

                $type = i\type_describe($step);

                $payload = i\iterable_type_check(
                    $iterator,
                    'callable',
                    new \UnexpectedValueException(
                        "Not all items created in compose step " . ($i + 1) . " ($type) are callable, got %s"
                    )
                );
            } catch (\Throwable $exception) {
                throw $this->buildException('compose', $i, $exception);
            }
        }

        return $payload;
    }

    /**
     * Create a database specific query object, applying the callbacks created in the compose step.
     *
     * @param iterable<callable> $payload
     * @param OptionInterface[]  $opts
     * @return mixed
     * @throws BuildQueryException
     */
    protected function build(iterable $payload, array $opts = [])
    {
        if ($this->buildSteps === []) {
            throw new \LogicException("Unusable query builder; no build step");
        }

        foreach ($this->buildSteps as $i => $step) {
            try {
                $payload = $step($payload, $opts);
            } catch (\Throwable $exception) {
                throw $this->buildException('build', $i, $exception);
            }
        }

        return $payload;
    }

    /**
     * Create a build exception.
     */
    protected function buildException(string $stage, int $stepNr, \Throwable $previous): BuildQueryException
    {
        $prop = $stage . 'Steps';

        $pos = ($stepNr + 1) . ' of ' . count($this->{$prop});
        $type = i\type_describe($this->{$prop}[$stepNr]);

        return new BuildQueryException("Query builder failed in {$stage} step {$pos} ($type)", 0, $previous);
    }
}
