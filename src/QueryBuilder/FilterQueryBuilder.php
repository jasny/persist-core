<?php

declare(strict_types=1);

namespace Jasny\DB\QueryBuilder;

use Jasny\DB\Filter\FilterItem;
use Jasny\DB\Option\OptionInterface;
use Jasny\DB\Filter\FilterParser;

/**
 * Query builder for filter queries.
 * @immutable
 *
 * @extends AbstractQueryBuilder<FilterItem>
 */
class FilterQueryBuilder extends AbstractQueryBuilder
{
    /** @var callable(object,FilterItem,OptionInterface[]):void */
    protected $compose;
    /** @var FilterParser|callable */
    protected $parser;

    /** @var array<string,callable> */
    protected array $fieldCompose = [];
    /** @var array<string,callable> */
    protected array $operatorCompose = [];

    /**
     * FilterQueryBuilder constructor.
     *
     * @param callable(object,FilterItem,OptionInterface[]):void                                             $compose
     * @param null|callable(iterable<string,mixed>|iterable<FilterItem>,OptionInterface[]):array<FilterItem> $parser
     */
    public function __construct(callable $compose, ?callable $parser = null)
    {
        $this->compose = $compose;
        $this->parser = $parser ?? new FilterParser();

        parent::__construct();
    }


    /**
     * Specify a custom logic for a (virtual) filter field.
     * The callable must accept the following arguments: ($accumulator, $filterItem, $opts, $next).
     *
     * @param string                                                     $field
     * @param callable(mixed,FilterItem,OptionInterface[],callable):void $compose
     * @return static
     */
    public function withCustomFilter(string $field, callable $compose): self
    {
        return $this->withPropertyKey('fieldCompose', $field, $compose);
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
     * @param callable(object,FilterItem,OptionInterface[],callable):void $compose
     * @return static
     */
    public function withCustomOperator(string $operator, callable $compose): self
    {
        return $this->withPropertyKey('operatorCompose', $operator, $compose);
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
     * @param mixed                                       $accumulator  Database specific query object.
     * @param iterable<FilterItem>|iterable<string,mixed> $filter
     * @param OptionInterface[]                           $opts
     */
    public function apply($accumulator, iterable $filter, array $opts = []): void
    {
        $parsedFilter = ($this->parser)($filter, $opts);

        parent::apply($accumulator, $parsedFilter, $opts);
    }

    /**
     * Apply each filter item to the accumulator.
     *
     * @param object               $accumulator
     * @param iterable<FilterItem> $filter
     * @param OptionInterface[]    $opts
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
     *
     * @param FilterItem $item
     * @return callable(object,FilterItem,OptionInterface[]):void
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
     *
     * @param callable $outer
     * @param callable $inner
     * @return \Closure&callable(object,FilterItem,array<OptionInterface>):void
     */
    private function nestCallback(callable $outer, callable $inner): \Closure
    {
        return static function (object $accumulator, FilterItem $item, array $opts) use ($outer, $inner): void {
            $next = static function (FilterItem $nextItem) use ($inner, $accumulator, $opts): void {
                $inner($accumulator, $nextItem, $opts);
            };

            $outer($accumulator, $item, $opts, $next);
        };
    }
}
