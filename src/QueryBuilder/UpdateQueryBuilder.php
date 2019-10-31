<?php

declare(strict_types=1);

namespace Jasny\DB\QueryBuilder;

use Jasny\DB\Option\OptionInterface;
use Jasny\DB\Update\UpdateInstruction;

/**
 * Query builder for update queries.
 * @immutable
 */
class UpdateQueryBuilder extends AbstractQueryBuilder
{
    /** @var array<string,callable> */
    protected array $operatorCompose = [];

    /**
     * UpdateQueryBuilder constructor.
     *
     * @param callable(object,UpdateInstruction,OptionInterface[])
     */
    public function __construct(callable $compose)
    {
        parent::__construct($compose);
    }

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
     * Specify a custom filter for an operator of an update instruction.
     * The callable must accept the following arguments: ($accumulator, $instruction, $opts, $next).
     *
     * @param string                                                            $operator
     * @param callable(mixed,UpdateInstruction,OptionInterface[],callable):void $apply
     * @return static
     */
    public function withCustomOperator(string $operator, callable $apply): self
    {
        return $this->withPropertyKey('operatorCompose', $operator, $apply);
    }

    /**
     * Remove a custom filter for a filter operator.
     *
     * @param string $operator
     * @return static
     */
    public function withoutCustomOperator(string $operator): self
    {
        return $this->withoutPropertyKey('operatorCompose', $operator);
    }


    /**
     * Apply each update instruction to the accumulator.
     */
    protected function applyCompose(object $accumulator, iterable $instructions, array $opts = []): void
    {
        foreach ($instructions as $instruction) {
            $compose = $this->getComposer($instruction);
            $compose($accumulator, $instruction, $opts);
        }
    }

    /**
     * Get a default or custom logic update instruction.
     */
    protected function getComposer(UpdateInstruction $item): callable
    {
        $defaultCompose = $this->compose;
        $operatorCompose = $this->operatorCompose[$item->getOperator()] ?? null;

        /** @var callable|null $operatorCompose */
        if ($operatorCompose === null) {
            return $defaultCompose;
        }

        return static function ($accumulator, $update, $opts) use ($operatorCompose, $defaultCompose) {
            $next = fn($nextUpdate) => $defaultCompose($accumulator, $nextUpdate, $opts);
            return $operatorCompose($accumulator, $update, $opts, $next);
        };
    }
}
