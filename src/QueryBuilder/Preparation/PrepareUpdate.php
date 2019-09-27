<?php

declare(strict_types=1);

namespace Jasny\DB\QueryBuilder\Preparation;

use Improved as i;
use Improved\IteratorPipeline\Pipeline;
use Jasny\DB\Exception\BuildQueryException;
use Jasny\DB\Option\OptionInterface;
use Jasny\DB\Update\UpdateInstruction;

/**
 * Callable steps for preparing filter items for a query.
 * @immutable
 */
class PrepareUpdate implements \IteratorAggregate
{
    /** @var array<callable(UpdateInstruction[],OptionInterface[]):UpdateInstruction[]> */
    protected array $steps;

    /**
     * PrepareUpdate constructor.
     *
     * @param callable(UpdateInstruction[],OptionInterface[]):UpdateInstruction[] ...$steps
     */
    public function __construct(callable ...$steps)
    {
        $this->steps = $steps;
    }

    /**
     * Get the steps for preparing the update.
     *
     * @return \ArrayIterator<callable(UpdateInstruction[],OptionInterface[]):UpdateInstruction[]>
     */
    public function getIterator(): \ArrayIterator
    {
        return new \ArrayIterator($this->steps);
    }

    /**
     * Prepare the update instructions
     *
     * @param UpdateInstruction[] $instructions
     * @param OptionInterface[]   $opts
     * @return UpdateInstruction[]
     * @throws BuildQueryException
     */
    public function __invoke(array $instructions, array $opts): array
    {
        foreach ($this->steps as $index => $step) {
            try {
                $instructions = $step($instructions, $opts);

                Pipeline::with($instructions)
                    ->typeCheck(UpdateInstruction::class, new \UnexpectedValueException())
                    ->walk();
            } catch (\Throwable $exception) {
                $step = 'step ' . ($index + 1) . ' of ' . count($this->steps);
                $type = i\type_describe($step);

                throw new BuildQueryException("Query builder failed in prepare {$step} ({$type})");
            }
        }

        return $instructions;
    }
}
