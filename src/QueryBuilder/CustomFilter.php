<?php declare(strict_types=1);

namespace Jasny\DB\QueryBuilder;

use Improved\IteratorPipeline\Pipeline;

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
     * @return iterable
     */
    public function __invoke(iterable $filter): iterable
    {
        return Pipeline::with($filter)
            ->map(function($orig, $info) {
                $field = is_array($info) ? ($info['field'] ?? null) : $info;

                return ($field === $this->field ? $this->apply : $orig);
            });
    }
}
