<?php

declare(strict_types=1);

namespace Jasny\DB\QueryBuilder\Step;

use Improved\IteratorPipeline\Pipeline;

/**
 * Custom filter for query builder compose step.
 * @immutable
 */
class CustomFilter
{
    protected \Closure $condition;
    protected \Closure $apply;

    /**
     * Create a query builder with a custom filter criteria.
     *
     * @param string|\Closure $fieldOrCondition
     * @param callable        $apply
     */
    public function __construct($fieldOrCondition, callable $apply)
    {
        $this->condition = $fieldOrCondition instanceof \Closure
            ? $fieldOrCondition
            : function ($field) use ($fieldOrCondition) {
                return $field === $fieldOrCondition;
            };

        $this->apply = \Closure::fromCallable($apply);
    }

    /**
     * Invoke the filter
     *
     * @param iterable $filter
     * @return iterable
     */
    public function __invoke(iterable $filter): iterable
    {
        return Pipeline::with($filter)
            ->map(function ($orig, $info): callable {
                $field = is_array($info) ? ($info['field'] ?? null) : $info;
                $operator = is_array($info) ? ($info['operator'] ?? null) : null;

                return (($this->condition)($field, $operator) ? $this->apply : $orig);
            });
    }
}
