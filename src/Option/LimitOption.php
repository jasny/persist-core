<?php declare(strict_types=1);

namespace Jasny\DB\Option;

/**
 * Limit the query result.
 */
class LimitOption implements QueryOption
{
    /**
     * @var int
     */
    protected $limit;

    /**
     * @var int
     */
    protected $offset;

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
     *
     * @return int
     */
    public function getLimit(): int
    {
        return $this->limit;
    }

    /**
     * Get the offset.
     *
     * @return int
     */
    public function getOffset(): int
    {
        return $this->offset;
    }
}
