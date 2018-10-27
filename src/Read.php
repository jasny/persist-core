<?php declare(strict_types=1);

namespace Jasny\DB;

/**
 * Service for full text search.
 */
interface Read
{
    /**
     * Query and fetch data.
     *
     * @param mixed $storage
     * @param array $filter
     * @param array $opts
     * @return Result
     */
    public function fetch($storage, array $filter = null, array $opts = []): Result;

    /**
     * Query and count result.
     *
     * @param mixed $storage
     * @param array $filter
     * @param array $opts
     * @return int
     */
    public function count($storage, array $filter = null, array $opts = []): int;
}
