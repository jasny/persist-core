<?php

declare(strict_types=1);

namespace Jasny\DB\QueryBuilder;

use Improved as i;
use Improved\IteratorPipeline\Pipeline;
use Jasny\DB\Option\OptionInterface;
use Jasny\DB\Update\UpdateInstruction;

/**
 * Query builder for update queries.
 * @immutable
 */
class UpdateQueryBuilder extends AbstractQueryBuilder
{
    /**
     * Get the prepare logic of the query builder.
     *
     * @return callable(UpdateInstruction[],OptionInterface[]):UpdateInstruction[]
     */
    public function getPreparation(): callable
    {
        return $this->prepare;
    }

    /**
     * Set the prepare logic of the query builder.
     *
     * @param callable(UpdateInstruction[],OptionInterface[]):UpdateInstruction[] $prepare
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
    public function withCustomLogic(string $field, callable $apply): self
    {
        return $this->withPropertyKey('fieldLogic', $field, $apply);
    }

    /**
     * Remove custom logic for a filter field.
     *
     * @param string $field
     * @return static
     */
    public function withoutCustomLogic(string $field): self
    {
        return $this->withoutPropertyKey('fieldLogic', $field);
    }

    /**
     * Specify a custom filter for an operator of an update instruction.
     * The callable must accept the following arguments: ($accumulator, $instruction, $opts, $next).
     *
     * @param string                                                            $operator
     * @param callable(mixed,UpdateInstruction,OptionInterface[],callable):void $apply
     * @return static
     */
    public function withCustomOperator(string $operator, callable $apply): self
    {
        return $this->withPropertyKey('operatorLogic', $operator, $apply);
    }

    /**
     * Remove a custom filter for a filter operator.
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
     * @param iterable          $update
     * @param OptionInterface[] $opts
     */
    public function apply($accumulator, iterable $update, array $opts = []): void
    {
        $updateArr = Pipeline::with($update)
            ->typeCheck(UpdateInstruction::class, new \UnexpectedValueException())
            ->toArray();

        $prepare = $this->getPreparation();
        $instructions = $prepare($updateArr, $opts);

        foreach ($instructions as $instruction) {
            $apply = $this->getLogicFor($instruction);
            $apply($accumulator, $instruction, $opts);
        }

        $finalize = $this->getFinalization();
        $finalize($accumulator, $opts);
    }

    /**
     * Get a default or custom logic update instruction.
     */
    protected function getLogicFor(object $item): callable
    {
        i\type_check($item, UpdateInstruction::class);

        $defaultLogic = $this->defaultLogic;
        $operatorLogic = $this->operatorLogic[$item->getOperator()] ?? null;

        /** @var callable|null $operatorLogic */
        if ($operatorLogic === null) {
            return $defaultLogic;
        }

        return static function ($accumulator, $update, $opts) use ($operatorLogic, $defaultLogic) {
            $next = fn($nextUpdate) => $defaultLogic($accumulator, $nextUpdate, $opts);
            return $operatorLogic($accumulator, $update, $opts, $next);
        };
    }
}
