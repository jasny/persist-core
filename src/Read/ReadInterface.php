<?php

declare(strict_types=1);

namespace Jasny\DB\Read;

use Improved\IteratorPipeline\PipelineBuilder;
use Jasny\DB\QueryBuilder\QueryBuilderInterface;
use Jasny\DB\Result;

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
     * Create a reader with a custom query builder.
     */
    public function withQueryBuilder(QueryBuilderInterface $queryBuilder): self;

    /**
     * Create a reader with a custom result builder.
     */
    public function withResultBuilder(PipelineBuilder $resultBuilder): self;


    /**
     * Query and fetch data.
     */
    public function fetch(array $filter = null, array $opts = []): Result;

    /**
     * Query and count result.
     */
    public function count(array $filter = null, array $opts = []): int;
}
