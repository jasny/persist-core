<?php

declare(strict_types=1);

namespace Persist\Option;

/**
 * Limit the query result.
 */
class LimitOption implements OptionInterface
{
    protected int $limit;
    protected int $offset;

    /**
     * Class constructor.
     *
     * @param int $limit
     * @param int $offset
     */
    public function __construct(int $limit, int $offset = 0)
    {
        $this->limit = $limit;
        $this->offset = $offset;
    }

    /**
     * Get the limit.
     */
    public function getLimit(): int
    {
        return $this->limit;
    }

    /**
     * Get the offset.
     */
    public function getOffset(): int
    {
        return $this->offset;
    }
}
