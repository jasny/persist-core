<?php

declare(strict_types=1);

namespace Jasny\DB\Result;

use Improved as i;
use Improved\IteratorPipeline\Pipeline;
use function Jasny\expect_type;
use function Jasny\get_type_description;

/**
 * Query result
 */
class Result extends Pipeline
{
    /**
     * @var int|\Closure
     */
    protected $totalCount;


    /**
     * Result constructor.
     *
     * @param iterable          $iterable
     * @param int|callable|null $totalCount
     */
    public function __construct(iterable $iterable, $totalCount = null)
    {
        expect_type($totalCount, ['int', 'callable', 'null']);

        parent::__construct($iterable);

        $this->totalCount = $totalCount;
    }

    /**
     * Resolve total count if it's still a Closure.
     *
     * @return void
     * @throws \UnexpectedValueException if total count closure didn't return a positive integer
     */
    protected function resolveTotalCount(): void
    {
        if (is_int($this->totalCount) || !is_callable($this->totalCount)) {
            return;
        }

        $count = i\function_call($this->totalCount);

        if ((!is_int($count) && !ctype_digit($count)) || $count < 0) {
            throw new \UnexpectedValueException(
                "Failed to get total count: " .
                "Expected a positive integer, got " . (is_int($count) ? $count : get_type_description($count))
            );
        }

        $this->totalCount = (int)$count;
    }

    /**
     * Get the total count of all the entities (if set was limited)
     *
     * @return int
     * @throws \BadMethodCallException if total count isn't set
     * @throws \UnexpectedValueException if total count closure didn't return a positive integer
     */
    public function getTotalCount(): int
    {
        if (!isset($this->totalCount)) {
            throw new \BadMethodCallException("Total count is not set");
        }

        $this->resolveTotalCount();

        return $this->totalCount;
    }
}
