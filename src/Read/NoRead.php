<?php

declare(strict_types=1);

namespace Jasny\DB\Read;

use Improved\IteratorPipeline\PipelineBuilder;
use Jasny\DB\Exception\UnsupportedFeatureException;
use Jasny\DB\QueryBuilder\QueryBuilderInterface;
use Jasny\DB\Result;

/**
 * Reading from storage is not supported.
 */
class NoRead implements ReadInterface
{
    /**
     * Get underlying storage object.
     *
     * @return null
     */
    public function getStorage()
    {
        return null;
    }

    /**
     * Does nothing.
     *
     * @param QueryBuilderInterface $queryBuilder
     * @return $this
     */
    public function withQueryBuilder(QueryBuilderInterface $queryBuilder): ReadInterface
    {
        return $this;
    }

    /**
     * Does nothing.
     *
     * @param PipelineBuilder $resultBuilder
     * @return $this
     */
    public function withResultBuilder(PipelineBuilder $resultBuilder): ReadInterface
    {
        return $this;
    }


    /**
     * Fetch is not supported.
     *
     * @throws UnsupportedFeatureException
     */
    public function fetch(array $filter = null, array $opts = []): Result
    {
        throw new UnsupportedFeatureException("Reading from storage is not supported");
    }

    /**
     * Count is not supported.
     *
     * @throws UnsupportedFeatureException
     */
    public function count(array $filter = null, array $opts = []): int
    {
        throw new UnsupportedFeatureException("Reading from storage is not supported");
    }
}
