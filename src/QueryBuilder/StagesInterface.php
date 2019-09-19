<?php

declare(strict_types=1);

namespace Jasny\DB\QueryBuilder;

/**
 * Query builder with customizable stages.
 *
 * The 4 stages are
 *  - prepare -> parse filters, mapping, casting
 *  - compose -> create filter function per field
 *  - build -> apply filter function
 *  - finalize -> reduce into a database query
 *
 * Each stage can have 0 or more callables. The callables of all stages are run consecutively.
 */
interface StagesInterface
{
    /**
     * Return a query builder with some steps removed.
     *
     * @param callable $matcher
     * @return static
     */
    public function withoutSteps(callable $matcher): self;


    /**
     * Create a query builder with a custom prepare step.
     *
     * @param callable $step
     * @return static
     */
    public function onPrepare(callable $step): self;

    /**
     * Create a query builder with a custom compose step.
     *
     * @param callable $step
     * @return static
     */
    public function onCompose(callable $step): self;

    /**
     * Create a query builder with a custom build step.
     *
     * @param callable $step
     * @return static
     */
    public function onBuild(callable $step): self;

    /**
     * Create a query builder with a custom finalize step.
     *
     * @param callable $step
     * @return static
     */
    public function onFinalize(callable $step): self;
}
