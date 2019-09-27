<?php

declare(strict_types=1);

namespace Jasny\DB\QueryBuilder\Finalization;

use Improved as i;
use Jasny\DB\Exception\BuildQueryException;
use Jasny\DB\Option\OptionInterface;

/**
 * Callable steps for finalizing a query before it's send to the DB.
 * @immutable
 */
class Finalize implements \IteratorAggregate
{
    /** @var array<callable(mixed,OptionInterface[]):void> */
    protected array $steps;

    /**
     * Finalize constructor.
     *
     * @param callable(mixed,OptionInterface[]):void ...$steps
     */
    public function __construct(callable ...$steps)
    {
        $this->steps = $steps;
    }

    /**
     * Get the steps for preparing the filter.
     *
     * @return \ArrayIterator<callable(mixed,OptionInterface[]):void>
     */
    public function getIterator(): \ArrayIterator
    {
        return new \ArrayIterator($this->steps);
    }

    /**
     * Apply steps to a filter.
     *
     * @param mixed             $accumulator
     * @param OptionInterface[] $opts
     * @throws BuildQueryException
     */
    public function __invoke($accumulator, array $opts): void
    {
        foreach ($this->steps as $index => $step) {
            try {
                $step($accumulator, $opts);
            } catch (\Throwable $exception) {
                $step = 'step ' . ($index + 1) . ' of ' . count($this->steps);
                $type = i\type_describe($stepNr !== null ? $this->{$prop}[$stepNr] : $this->{$prop});

                throw new BuildQueryException("Query builder failed in finalize {$step} ({$type})");
            }
        }
    }
}
