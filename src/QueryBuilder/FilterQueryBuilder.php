<?php

declare(strict_types=1);

namespace Jasny\DB\QueryBuilder;

use Improved as i;
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

    /**
     * FilterQueryBuilder constructor.
     *
     * @param callable(mixed,FilterItem,OptionInterface[]):void   $apply
     * @param null|callable(array,OptionInterface[]):FilterItem[] $parser    Defaults to a `FilterParser`.
     */
    public function __construct(callable $apply, ?callable $parser = null)
    {
        $this->parser = $parser ?? new FilterParser();

        parent::__construct($apply);
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
        return $this->withPropertyKey('fieldLogic', $field, $apply);
    }

    /**
     * Remove custom logic for a filter field.
     *
     * @param string $field
     * @return static
     */
    public function withoutCustomFilter(string $field): self
    {
        return $this->withoutPropertyKey('fieldLogic', $field);
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
        return $this->withPropertyKey('operatorLogic', $operator, $apply);
    }

    /**
     * Remove custom logic for a filter operator.
     *
     * @param string $operator
     * @return static
     */
    public function withoutCustomOperator(string $operator): self
    {
        return $this->withoutPropertyKey('operatorLogic', $operator);
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
     * Get a default or custom logic for a filter item.
     */
    protected function getLogicFor(object $item): callable
    {
        i\type_check($item, FilterItem::class);

        $logic = $this->defaultLogic;

        $operatorLogic = $this->operatorLogic[$item->getOperator()] ?? null;
        if ($operatorLogic !== null) {
            $logic = $this->nestCustomFilterCallback($operatorLogic, $logic);
        }

        $fieldLogic = $this->fieldLogic[$item->getField()] ?? null;
        if ($fieldLogic !== null) {
            $logic = $this->nestCustomFilterCallback($fieldLogic, $logic);
        }

        return $logic;
    }

    /**
     * Nest callback for custom filter.
     */
    private function nestCustomFilterCallback(callable $outer, callable $inner): \Closure
    {
        return static function ($accumulator, $item, $opts) use ($outer, $inner) {
            $next = fn($nextItem) => $inner($accumulator, $nextItem, $opts);

            return $outer($accumulator, $item, $opts, $next);
        };
    }
}
