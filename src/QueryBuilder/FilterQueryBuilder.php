<?php

declare(strict_types=1);

namespace Jasny\DB\QueryBuilder;

use Jasny\DB\Exception\BuildQueryException;
use Jasny\DB\Filter\FilterItem;
use Jasny\DB\Option\OptionInterface;
use Jasny\DB\Filter\FilterParser;
use Jasny\DB\QueryBuilder\Finalization\Finalize;
use Jasny\DB\QueryBuilder\Preparation\PrepareFilter;
use Jasny\Immutable;

/**
 * Query builder for filter queries.
 * @immutable
 */
class FilterQueryBuilder implements QueryBuilderInterface
{
    use Immutable\With;

    /** @var FilterParser|callable */
    protected $parser;

    /** @var PrepareFilter|callable */
    protected $prepare;
    /** @var Finalize|callable */
    protected $finalize;

    /** @var callable(mixed,FilterItem,OptionInterface[]):void */
    protected $defaultLogic;
    /** @var array<string,callable> */
    protected array $fieldLogic = [];
    /** @var array<string,callable> */
    protected array $operatorLogic = [];


    /**
     * FilterQueryBuilder constructor.
     *
     * @param callable(mixed,FilterItem,OptionInterface[]):void   $apply
     * @param null|callable(array,OptionInterface[]):FilterItem[] $parser    Defaults to a `FilterParser`.
     */
    public function __construct(callable $apply, ?callable $parser = null)
    {
        $this->parser = $parser ?? new FilterParser();
        $this->defaultLogic = $apply;
    }


    /**
     * Get the prepare logic of the query builder.
     *
     * @return PrepareFilter|callable(FilterItem[],OptionInterface[]):FilterItem[]
     */
    public function getPreparation(): callable
    {
        $this->prepare ??= new PrepareFilter();

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
     * Get the finalize logic of the query builder.
     *
     * @return Finalize|callable(mixed,OptionInterface[]):void
     */
    public function getFinalization(): callable
    {
        $this->finalize ??= new Finalize();

        return $this->finalize;
    }

    /**
     * Set the finalize logic of the query builder.
     *
     * @param callable(mixed,OptionInterface[]):void $finalize
     * @return static
     */
    public function withFinalization(callable $finalize): self
    {
        return $this->withProperty('finalize', $finalize);
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
    public function withCustomFilterOperator(string $operator, callable $apply): self
    {
        return $this->withPropertyKey('operatorLogic', $operator, $apply);
    }

    /**
     * Remove custom logic for a filter operator.
     *
     * @param string $operator
     * @return static
     */
    public function withoutCustomFilterOperator(string $operator): self
    {
        return $this->withoutPropertyKey('operatorLogic', $operator);
    }


    /**
     * Apply instructions to given query.
     *
     * @param mixed             $accumulator  Database specific query object.
     * @param iterable          $filter
     * @param OptionInterface[] $opts
     * @throws BuildQueryException
     */
    public function apply($accumulator, iterable $filter, array $opts = []): void
    {
        $parsedFilter = ($this->parser)($filter, $opts);

        $prepare = $this->getPreparation();
        $filterItems = $prepare($parsedFilter, $opts);

        foreach ($filterItems as $filterItem) {
            $apply = $this->getLogicFor($filterItem);
            $apply($accumulator, $filterItem, $opts);
        }

        $finalize = $this->getFinalization();
        $finalize($accumulator, $opts);
    }

    /**
     * Get a default or custom logic for a filter item.
     */
    protected function getLogicFor(FilterItem $item): callable
    {
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
