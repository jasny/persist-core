<?php

declare(strict_types=1);

namespace Jasny\DB\QueryBuilder;

use Jasny\DB\Exception\BuildQueryException;
use Jasny\DB\Filter\FilterItem;
use Jasny\DB\Option\OptionInterface;
use Jasny\Immutable;

/**
 * Base class for filter and update query builders.
 * @internal
 */
abstract class AbstractQueryBuilder implements QueryBuilderInterface
{
    use Immutable\With;

    /** @var callable */
    protected $prepare;
    /** @var callable */
    protected $finalize;

    /** @var callable */
    protected $defaultLogic;
    /** @var array<string,callable> */
    protected array $fieldLogic = [];
    /** @var array<string,callable> */
    protected array $operatorLogic = [];


    /**
     * Get a default or custom logic update instruction.
     *
     * @param object $item
     */
    abstract protected function getLogicFor(object $item): callable;


    /**
     * FilterQueryBuilder constructor.
     *
     * @param callable(mixed,array,OptionInterface[]):void $apply
     */
    public function __construct(callable $apply)
    {
        $this->defaultLogic = $apply;

        // nop functions
        $this->prepare = fn(array $filterItems, array $options): array => $filterItems;
        $this->finalize = function ($accumulator, array $options): void {
        };
    }


    /**
     * Get the finalize logic of the query builder.
     *
     * @return callable(mixed,OptionInterface[]):void
     */
    public function getFinalization(): callable
    {
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
     * Apply instructions to given query.
     *
     * @param mixed             $accumulator  Database specific query object.
     * @param iterable          $instructions
     * @param OptionInterface[] $opts
     */
    public function apply($accumulator, iterable $instructions, array $opts = []): void
    {
        $prepare = $this->getPreparation();
        $instructions = $prepare($instructions, $opts);

        foreach ($instructions as $instruction) {
            $apply = $this->getLogicFor($instruction);
            $apply($accumulator, $instruction, $opts);
        }

        $finalize = $this->getFinalization();
        $finalize($accumulator, $opts);
    }
}
