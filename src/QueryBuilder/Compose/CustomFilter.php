<?php

declare(strict_types=1);

namespace Jasny\DB\QueryBuilder\Compose;

use Improved as i;
use Improved\IteratorPipeline\Pipeline;

/**
 * Custom filter for query builder compose step.
 *
 * The condition may be a field name (string) or a Closure with signature
 *
 *     function (string $field, string $operator): bool
 *
 * If the condition closure returns `true`, the apply function will be used. For `false`, the logic of applied that
 *   filter item remains unchanged.
 *
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
        i\type_check($fieldOrCondition, ['string', \Closure::class]);

        if ($fieldOrCondition instanceof \Closure) {
            $this->condition = $fieldOrCondition;
        } else {
            $this->condition = static function ($field) use ($fieldOrCondition) {
                return $field === $fieldOrCondition;
            };
        }

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
            ->map(function ($orig, $info): ?callable {
                $field = is_array($info) ? ($info['field'] ?? null) : $info;
                $operator = is_array($info) ? ($info['operator'] ?? '') : '';

                $matches = $field !== null && ($this->condition)($field, $operator);

                return $matches ? $this->apply : $orig;
            });
    }
}
