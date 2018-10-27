<?php declare(strict_types=1);

namespace Jasny\DB\Read;

use Improved\IteratorPipeline\PipelineBuilder;
use Jasny\DB\QueryBuilder\QueryBuilding;

/**
 * Service has query builder and result builder.
 */
interface WithBuilders
{
    /**
     * Create a reader with a custom query builder.
     *
     * @param QueryBuilding $queryBuilder
     * @return mixed
     */
    public function withQueryBuilder(QueryBuilding $queryBuilder);

    /**
     * Create a reader with a custom result builder.
     *
     * @param PipelineBuilder $resultBuilder
     * @return mixed
     */
    public function withResultBuilder(PipelineBuilder $resultBuilder);
}
