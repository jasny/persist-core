<?php

declare(strict_types=1);

namespace Jasny\DB\Read;

use Improved\IteratorPipeline\PipelineBuilder;
use Jasny\DB\QueryBuilder;

/**
 * Service has query builder and result builder.
 */
interface WithBuilders
{
    /**
     * Create a reader with a custom query builder.
     *
     * @param QueryBuilder $queryBuilder
     * @return mixed
     */
    public function withQueryBuilder(QueryBuilder $queryBuilder);

    /**
     * Create a reader with a custom result builder.
     *
     * @param PipelineBuilder $resultBuilder
     * @return mixed
     */
    public function withResultBuilder(PipelineBuilder $resultBuilder);
}
