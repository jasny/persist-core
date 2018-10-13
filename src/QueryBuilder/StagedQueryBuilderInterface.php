<?php

declare(strict_types=1);

namespace Jasny\DB\QueryBuilder;

use Jasny\DB\FieldMap\FieldMapInterface;

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
interface StagedQueryBuilderInterface extends QueryBuilderInterface
{
    /**
     * Create a query builder with a custom filter criteria.
     *
     * @param string   $field
     * @param callable $apply
     * @return static
     */
    public function withFilter(string $field, callable $apply);


    /**
     * Create a query builder with a custom prepare step.
     *
     * @param callable $step
     * @return static
     */
    public function onPrepare(callable $step);

    /**
     * Create a query builder with a custom compose step.
     *
     * @param callable $step
     * @return static
     */
    public function onCompose(callable $step);

    /**
     * Create a query builder with a custom build step.
     *
     * @param callable $step
     * @return static
     */
    public function onBuild(callable $step);

    /**
     * Create a query builder with a custom finalize step.
     *
     * @param callable $step
     * @return static
     */
    public function onFinalize(callable $step);
}
