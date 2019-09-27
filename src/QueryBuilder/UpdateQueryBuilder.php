<?php

declare(strict_types=1);

namespace Jasny\DB\QueryBuilder;

use Improved\IteratorPipeline\Pipeline;
use Jasny\DB\Exception\BuildQueryException;
use Jasny\DB\Option\OptionInterface;
use Jasny\DB\QueryBuilder\Finalization\Finalize;
use Jasny\DB\QueryBuilder\Preparation\PrepareFilter;
use Jasny\DB\QueryBuilder\Preparation\PrepareUpdate;
use Jasny\DB\Update\UpdateInstruction;
use Jasny\Immutable;

/**
 * Query builder for update queries.
 * @immutable
 */
class UpdateQueryBuilder implements QueryBuilderInterface
{
    use Immutable\With;

    /** @var PrepareFilter|callable */
    protected $prepare;
    /** @var Finalize|callable */
    protected $finalize;

    /** @var callable(mixed,UpdateInstruction[],OptionInterface[]):void */
    protected $defaultLogic;
    /** @var array<string,callable> */
    protected array $operatorLogic = [];

    /**
     * UpdateQueryBuilder constructor.
     *
     * @param callable(mixed,UpdateInstruction[],OptionInterface[]):void $apply
     */
    public function __construct(callable $apply)
    {
        $this->defaultLogic = $apply;
    }


    /**
     * Get the prepare logic of the query builder.
     *
     * @return PrepareUpdate|callable(UpdateInstruction[],OptionInterface[]):UpdateInstruction[]
     */
    public function getPreparation(): callable
    {
        $this->prepare ??= new PrepareUpdate();

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
     * Specify a custom filter for an operator of an update instruction.
     * The callable must accept the following arguments: ($accumulator, $instruction, $opts, $next).
     *
     * @param string                                                            $operator
     * @param callable(mixed,UpdateInstruction,OptionInterface[],callable):void $apply
     * @return static
     */
    public function withCustomUpdateOperator(string $operator, callable $apply): self
    {
        return $this->withPropertyKey('operatorLogic', $operator, $apply);
    }

    /**
     * Remove a custom filter for a filter operator.
     *
     * @param string $operator
     * @return static
     */
    public function withoutCustomUpdateOperator(string $operator): self
    {
        return $this->withoutPropertyKey('operatorLogic', $operator);
    }


    /**
     * Apply instructions to given query.
     *
     * @param mixed             $accumulator  Database specific query object.
     * @param iterable          $update
     * @param OptionInterface[] $opts
     * @throws BuildQueryException
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
    protected function getLogicFor(UpdateInstruction $item): callable
    {
        $defaultLogic = $this->defaultLogic;
        $operatorLogic = $this->operatorLogic[$item->getOperator()] ?? null;

        if ($operatorLogic === null) {
            return $defaultLogic;
        }

        return static function ($accumulator, $update, $opts) use ($operatorLogic, $defaultLogic) {
            return $operatorLogic($accumulator, $update, $opts, $defaultLogic);
        };
    }
}
