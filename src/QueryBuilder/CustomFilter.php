<?php

declare(strict_types=1);

namespace Jasny\DB\QueryBuilder;

/**
 * Custom filter for query builder compose step
 */
class CustomFilter
{
    /**
     * @var string
     */
    protected $field;

    /**
     * @var callable
     */
    protected $apply;


    /**
     * Create a query builder with a custom filter criteria.
     *
     * @param string   $field
     * @param callable $apply
     */
    public function __construct(string $field, callable $apply)
    {
        $this->field = $field;
        $this->apply = $apply;
    }

    /**
     * Invoke the filter
     *
     * @param iterable $filter
     * @return \Generator
     */
    public function __invoke(iterable $filter): \Generator
    {
        foreach ($filter as $info => $orig) {
            $field = is_array($info) ? ($info['field'] ?? null) : $info;

            yield $info => ($field === $this->field ? $this->apply : $orig);
        }
    }
}
