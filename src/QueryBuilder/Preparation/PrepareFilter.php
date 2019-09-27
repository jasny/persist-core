<?php

declare(strict_types=1);

namespace Jasny\DB\QueryBuilder\Preparation;

use Improved as i;
use Improved\IteratorPipeline\Pipeline;
use Jasny\DB\Exception\BuildQueryException;
use Jasny\DB\Filter\FilterItem;
use Jasny\DB\Option\OptionInterface;

/**
 * Callable steps for preparing filter items for a query.
 * @immutable
 */
class PrepareFilter implements \IteratorAggregate
{
    /** @var array<callable(FilterItem[],OptionInterface[]):FilterItem[]> */
    protected array $steps;

    /**
     * PrepareFilter constructor.
     *
     * @param callable(FilterItem[],OptionInterface[]):FilterItem[] ...$steps
     */
    public function __construct(callable ...$steps)
    {
        $this->steps = $steps;
    }

    /**
     * Get the steps for preparing the filter.
     *
     * @return \ArrayIterator<callable(FilterItem[],OptionInterface[]):FilterItem[]>
     */
    public function getIterator(): \ArrayIterator
    {
        return new \ArrayIterator($this->steps);
    }

    /**
     * Prepare the filter items.
     *
     * @param FilterItem[]      $filterItems
     * @param OptionInterface[] $opts
     * @return FilterItem[]
     * @throws BuildQueryException
     */
    public function __invoke(array $filterItems, array $opts): array
    {
        foreach ($this->steps as $index => $step) {
            try {
                $filterItems = $step($filterItems, $opts);
                Pipeline::with($filterItems)->typeCheck(FilterItem::class, new \UnexpectedValueException())->walk();
            } catch (\Throwable $exception) {
                $step = 'step ' . ($index + 1) . ' of ' . count($this->steps);
                $type = i\type_describe($step);

                throw new BuildQueryException("Query builder failed in prepare {$step} ({$type})");
            }
        }

        return $filterItems;
    }
}
