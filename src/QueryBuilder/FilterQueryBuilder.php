<?php

declare(strict_types=1);

namespace Jasny\DB\QueryBuilder;

use Jasny\DB\Filter\FilterItem;
use Jasny\DB\Option\OptionInterface;
use Jasny\DB\Filter\FilterParser;

/**
 * Query builder for filter queries.
 * @immutable
 */
class FilterQueryBuilder extends AbstractQueryBuilder
{
    /** @var FilterParser|callable */
    protected $parser;

    /** @var array<string,callable> */
    protected array $fieldCompose = [];
    /** @var array<string,callable> */
    protected array $operatorCompose = [];

    /**
     * FilterQueryBuilder constructor.
     *
     * @param callable(mixed,FilterItem,OptionInterface[]):void   $compose
     * @param null|callable(array,OptionInterface[]):FilterItem[] $parser    Defaults to a `FilterParser`.
     */
    public function __construct(callable $compose, ?callable $parser = null)
    {
        $this->parser = $parser ?? new FilterParser();

        parent::__construct($compose);
    }


    /**
     * Get the prepare logic of the query builder.
     *
     * @return callable(FilterItem[],OptionInterface[]):FilterItem[]
     */
    public function getPreparation(): callable
    {
        return $this->prepare;
    }

    /**
     * Set the prepare logic of the query builder.
     *
     * @param callable(FilterItem[],OptionInterface[]):FilterItem[] $prepare
     * @return static
     */
    public function withPreparation(callable $prepare): self
    {
        return $this->withProperty('prepare', $prepare);
    }


    /**
     * Specify a custom logic for a (virtual) filter field.
     * The callable must accept the following arguments: ($accumulator, $filterItem, $opts, $next).
     *
     * @param string                                                     $field
     * @param callable(mixed,FilterItem,OptionInterface[],callable):void $apply
     * @return static
     */
    public function withCustomFilter(string $field, callable $apply): self
    {
        return $this->withPropertyKey('fieldCompose', $field, $apply);
    }

    /**
     * Remove custom logic for a filter field.
     *
     * @param string $field
     * @return static
     */
    public function withoutCustomFilter(string $field): self
    {
        return $this->withoutPropertyKey('fieldCompose', $field);
    }

    /**
     * Specify custom logic for a filter operator.
     * The callable must accept the following arguments: ($accumulator, $filterItem, $opts, $next).
     *
     * @param string                                                     $operator
     * @param callable(mixed,FilterItem,OptionInterface[],callable):void $apply
     * @return static
     */
    public function withCustomOperator(string $operator, callable $apply): self
    {
        return $this->withPropertyKey('operatorCompose', $operator, $apply);
    }

    /**
     * Remove custom logic for a filter operator.
     *
     * @param string $operator
     * @return static
     */
    public function withoutCustomOperator(string $operator): self
    {
        return $this->withoutPropertyKey('operatorCompose', $operator);
    }


    /**
     * Apply instructions to given query.
     *
     * @param mixed             $accumulator  Database specific query object.
     * @param iterable          $filter
     * @param OptionInterface[] $opts
     */
    public function apply($accumulator, iterable $filter, array $opts = []): void
    {
        $parsedFilter = ($this->parser)($filter, $opts);

        parent::apply($accumulator, $parsedFilter, $opts);
    }

    /**
     * Apply each filter item to the accumulator.
     */
    protected function applyCompose(object $accumulator, iterable $filter, array $opts = []): void
    {
        foreach ($filter as $filterItem) {
            $compose = $this->getComposer($filterItem);
            $compose($accumulator, $filterItem, $opts);
        }
    }

    /**
     * Get the default or a custom compose function for a filter item.
     */
    protected function getComposer(FilterItem $item): callable
    {
        $compose = $this->compose;

        $operatorCompose = $this->operatorCompose[$item->getOperator()] ?? null;
        if ($operatorCompose !== null) {
            $compose = $this->nestCallback($operatorCompose, $compose);
        }

        $fieldCompose = $this->fieldCompose[$item->getField()] ?? null;
        if ($fieldCompose !== null) {
            $compose = $this->nestCallback($fieldCompose, $compose);
        }

        return $compose;
    }

    /**
     * Nest callback for custom filter.
     */
    private function nestCallback(callable $outer, callable $inner): \Closure
    {
        return static function ($accumulator, $item, $opts) use ($outer, $inner) {
            $next = fn($nextItem) => $inner($accumulator, $nextItem, $opts);

            return $outer($accumulator, $item, $opts, $next);
        };
    }
}
