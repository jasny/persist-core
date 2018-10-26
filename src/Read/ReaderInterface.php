<?php declare(strict_types=1);

namespace Jasny\DB\Read;

use Improved\IteratorPipeline\PipelineBuilder;
use Jasny\DB\QueryBuilder\QueryBuilderInterface;
use Jasny\DB\Result\Result;

/**
 * Service for full text search.
 */
interface ReaderInterface
{
    /**
     * Create a reader with a custom query builder.
     *
     * @param QueryBuilderInterface $queryBuilder
     * @return mixed
     */
    public function withQueryBuilder(QueryBuilderInterface $queryBuilder);

    /**
     * Create a reader with a custom result builder.
     *
     * @param PipelineBuilder $resultBuilder
     * @return mixed
     */
    public function withResultBuilder(PipelineBuilder $resultBuilder);


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
