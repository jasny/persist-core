<?php declare(strict_types=1);

namespace Jasny\DB\Read;

use Improved\IteratorPipeline\PipelineBuilder;
use Jasny\DB\QueryBuilder\QueryBuilderInterface;
use Jasny\DB\Exception\UnsupportedFeatureException;

/**
 * Reading from storage is not supported.
 */
class NoRead implements ReaderInterface
{
    /**
     * Create a CRUD service with a custom query builder.
     *
     * @param QueryBuilderInterface $queryBuilder
     * @return mixed
     */
    public function withQueryBuilder(QueryBuilderInterface $queryBuilder)
    {
        return $this;
    }

    /**
     * Create a reader with a custom result builder.
     *
     * @param PipelineBuilder $resultBuilder
     * @return mixed
     */
    public function withResultBuilder(PipelineBuilder $resultBuilder)
    {
        return $this;
    }


    /**
     * Fetch is not supported
     *
     * @param mixed $storage
     * @param array $filter
     * @param array $opts
     * @return Result
     */
    public function fetch($storage, array $filter = null, array $opts = []): Result
    {
        throw new UnsupportedFeatureException("Reading from storage is not supported");
    }

    /**
     * Count is not supported
     *
     * @param mixed $storage
     * @param array $filter
     * @param array $opts
     * @return int
     */
    public function count($storage, array $filter = null, array $opts = []): int
    {
        throw new UnsupportedFeatureException("Reading from storage is not supported");
    }
}
