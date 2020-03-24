<?php

declare(strict_types=1);

namespace Jasny\DB\Reader;

use Jasny\DB\Option\OptionInterface;
use Jasny\DB\Result\Result;
use Psr\Log\LoggerInterface;

/**
 * Service for full text search.
 *
 * @template TValue
 */
interface ReadInterface
{
    /**
     * Get underlying storage object.
     * This is DB implementation dependent.
     *
     * @return mixed
     */
    public function getStorage();

    /**
     * Enable (debug) logging.
     *
     * @return static
     */
    public function withLogging(LoggerInterface $logger);


    /**
     * Query and fetch data.
     *
     * @param array<string,mixed> $filter
     * @param OptionInterface[]   $opts
     * @return Result
     *
     * @phpstan-param array<string,mixed> $filter
     * @phpstan-param OptionInterface[]   $opts
     * @phpstan-return Result<TValue>
     */
    public function fetch(array $filter = [], array $opts = []): Result;

    /**
     * Query and count result.
     *
     * @param array<string,mixed> $filter
     * @param OptionInterface[]   $opts
     * @return int
     */
    public function count(array $filter = [], array $opts = []): int;
}
