<?php

declare(strict_types=1);

namespace Jasny\DB\QueryBuilder;

use Jasny\DB\Option\OptionInterface;
use Jasny\DB\Update\UpdateInstruction;
use Jasny\Immutable;

/**
 * Query builder for update queries.
 * @immutable
 *
 * @template TQuery of object
 * @extends AbstractQueryBuilder<TQuery,UpdateInstruction>
 */
class UpdateQueryBuilder extends AbstractQueryBuilder
{
    use Immutable\With;

    /**
     * @var callable
     * @phpstan-param callable(TQuery,UpdateInstruction,array<OptionInterface>):void
     */
    protected $compose;

    /**
     * @var array<string,callable>
     * @phpstan-param array<string,callable(TQuery,UpdateInstruction,array<OptionInterface>,callable):void>
     */
    protected array $operatorCompose = [];


    /**
     * UpdateQueryBuilder constructor.
     *
     * @param callable(object,UpdateInstruction,array<OptionInterface>):void $compose
     */
    public function __construct(callable $compose)
    {
        $this->compose = $compose;

        parent::__construct();
    }

    /**
     * Specify a custom filter for an operator of an update instruction.
     * The callable must accept the following arguments: ($accumulator, $instruction, $opts, $next).
     *
     * @param string   $operator
     * @param callable $apply
     * @return static
     *
     * @phpstan-param string                                                             $operator
     * @phpstan-param callable(TQuery,UpdateInstruction,OptionInterface[],callable):void $apply
     * @phpstan-return static
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
     *
     * @param object                      $accumulator
     * @param iterable<UpdateInstruction> $instructions
     * @param OptionInterface[]           $opts
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
     *
     * @param UpdateInstruction $item
     * @return callable(object,UpdateInstruction,array<OptionInterface>):void
     */
    protected function getComposer(UpdateInstruction $item): callable
    {
        $defaultCompose = $this->compose;
        $operatorCompose = $this->operatorCompose[$item->getOperator()] ?? null;

        if ($operatorCompose === null) {
            return $defaultCompose;
        }

        return $this->nestCallback($operatorCompose, $defaultCompose);
    }


    /**
     * Nest callback for custom filter.
     *
     * @param callable $outer
     * @param callable $inner
     * @return \Closure&callable(object,UpdateInstruction,array<OptionInterface>):void
     */
    private function nestCallback(callable $outer, callable $inner): \Closure
    {
        return static function (object $accumulator, UpdateInstruction $item, array $opts) use ($outer, $inner): void {
            $next = static function (UpdateInstruction $nextItem) use ($inner, $accumulator, $opts): void {
                $inner($accumulator, $nextItem, $opts);
            };

            $outer($accumulator, $item, $opts, $next);
        };
    }
}
