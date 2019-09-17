<?php

declare(strict_types=1);

namespace Jasny\DB\Read;

use Improved\IteratorPipeline\PipelineBuilder;
use Jasny\DB\Option\OptionInterface;
use Jasny\DB\QueryBuilder\QueryBuilderInterface;
use Jasny\DB\Result;
use Psr\Log\LoggerInterface;

/**
 * Service for full text search.
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
     * Set logger to enable logging.
     *
     * @return static
     */
    public function withLogging(LoggerInterface $logger);


    /**
     * Create a reader with a custom query builder.
     *
     * @return static
     */
    public function withQueryBuilder(QueryBuilderInterface $queryBuilder);

    /**
     * Create a reader with a custom result builder.
     *
     * @return static
     */
    public function withResultBuilder(PipelineBuilder $resultBuilder);


    /**
     * Query and fetch data.
     *
     * @param array<string, mixed> $filter
     * @param OptionInterface[]    $opts
     * @return Result
     */
    public function fetch(array $filter = [], array $opts = []): Result;

    /**
     * Query and count result.
     *
     * @param array<string, mixed> $filter
     * @param OptionInterface[]    $opts
     * @return int
     */
    public function count(array $filter = [], array $opts = []): int;
}
