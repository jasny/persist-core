<?php

declare(strict_types=1);

namespace Jasny\DB\QueryBuilder;

use Improved as i;
use Jasny\DB\Exception\BuildQueryException;
use Jasny\DB\Option\OptionInterface;

/**
 * Query builder with customizable stages. The 4 stages are
 *
 * - prepare -> parse filters, mapping, casting
 *   The prepare steps are callables that takes an iterable and must return an iterable.
 *
 * - compose -> create filter function per field
 *   The compose steps are callables that takes an iterable and must return an iterable with callables.
 *
 * - build  -> reduce into a database query
 *   The build step takes an iterable with callables and applies them to a database specific query object which
 *   functions as accumulator.
 *
 * - finalize -> modify the database query
 *   Apply additional logic based to modify the database query object.
 *
 * @immutable
 */
class StagedQueryBuilder implements QueryBuilderInterface
{
    /** @var array<int,callable(iterable,OptionInterface[]):iterable> */
    protected array $prepareSteps = [];

    /** @var array<int,callable(iterable,OptionInterface[]):iterable> */
    protected array $composeSteps = [];

    /** @var callable(object,iterable,OptionInterface[]):void */
    protected $buildStep;

    /** @var array<int,callable(object,OptionInterface[]):void> */
    protected array $finalizeSteps = [];


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
        if (isset($this->buildStep)) {
            throw new \LogicException("Query builder can only have one build step");
        }

        $clone = clone $this;
        $clone->buildStep = $step;

        return $clone;
    }

    /**
     * Return a query builder with the build step removed.
     *
     * @return static
     */
    public function withoutBuild(): self
    {
        if (!isset($this->buildStep)) {
            return $this;
        }

        $clone = clone $this;
        unset($clone->buildStep);

        return $clone;
    }


    /**
     * Return a query builder with an additional finalize step.
     *
     * @param callable $step
     * @return static
     */
    public function onFinalize(callable $step): self
    {
        return $this->withAddedStep('finalize', $step);
    }

    /**
     * Return a query builder with some or all finalize steps removed.
     *
     * @param null|callable(string,callable):bool $matcher  Return `true` to remove the step.
     * @return static
     */
    public function withoutFinalize(?callable $matcher = null): self
    {
        return $this->withoutSteps('finalize', $matcher);
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
     * @param object            $accumulator  Database specific query object.
     * @param iterable          $filter
     * @param OptionInterface[] $opts
     * @throws BuildQueryException
     */
    public function apply(object $accumulator, iterable $filter, array $opts = []): void
    {
        $prepared = $this->prepare($filter, $opts);
        $compose = $this->compose($prepared, $opts);
        $this->build($accumulator, $compose, $opts);
        $this->finalize($accumulator, $opts);
    }

    /**
     * Alias of `apply()`.
     *
     * @param object            $accumulator  Database specific query object.
     * @param iterable          $filter
     * @param OptionInterface[] $opts
     * @throws BuildQueryException
     */
    final public function __invoke(object $accumulator, iterable $filter, array $opts = []): void
    {
        $this->apply($accumulator, $filter, $opts);
    }

    /**
     * Parse filters and apply mapping.
     *
     * @param iterable          $payload
     * @param OptionInterface[] $opts
     * @return iterable
     * @throws BuildQueryException
     */
    protected function prepare(iterable $payload, array &$opts = []): iterable
    {
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
                $unexpectedItemException = new \UnexpectedValueException("Not all items created in compose step "
                    . ($i + 1) . " (" . i\type_describe($step) . ") are callable, got %s");

                $iterator = i\type_check($step($payload, $opts), 'iterable', $unexpectedException);
                $payload = i\iterable_type_check($iterator, 'callable', $unexpectedItemException);
            } catch (\Throwable $exception) {
                throw $this->buildException('compose', $i, $exception);
            }
        }

        return $payload;
    }

    /**
     * Create a database specific query object, applying the callbacks created in the compose step.
     *
     * @param object             $accumulator
     * @param iterable<callable> $compose
     * @param OptionInterface[]  $opts
     * @throws BuildQueryException
     */
    protected function build(object $accumulator, iterable $compose, array $opts = []): void
    {
        if (!isset($this->buildStep)) {
            throw new \LogicException("Unusable query builder; no build step");
        }

        try {
            ($this->buildStep)($accumulator, $compose, $opts);
        } catch (\Throwable $exception) {
            throw $this->buildException('build', null, $exception);
        }
    }

    /**
     * Modify the database specific query object.
     *
     * @param object             $accumulator
     * @param OptionInterface[]  $opts
     * @throws BuildQueryException
     */
    protected function finalize(object $accumulator, array $opts = []): void
    {
        foreach ($this->finalizeSteps as $i => $step) {
            try {
                $step($accumulator, $opts);
            } catch (\Throwable $exception) {
                throw $this->buildException('finalize', $i, $exception);
            }
        }
    }

    /**
     * Create a build exception.
     */
    protected function buildException(string $stage, ?int $stepNr, \Throwable $previous): BuildQueryException
    {
        $prop = $stage . ($stepNr === null ? 'Step' : 'Steps');

        $step = $stepNr !== null ? 'step ' . ($stepNr + 1) . ' of ' . count($this->{$prop}) : 'step';
        $type = i\type_describe($stepNr !== null ? $this->{$prop}[$stepNr] : $this->{$prop});

        return new BuildQueryException("Query builder failed in {$stage} {$step} ($type)", 0, $previous);
    }
}
