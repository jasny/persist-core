<?php

declare(strict_types=1);

namespace Jasny\DB\Search;

use Jasny\DB\QueryBuilder\QueryBuilderInterface;
use Jasny\DB\Result\Result;

/**
 * Service for full text search.
 */
interface SearchInterface
{
    /**
     * Create a CRUD service with a custom query builder.
     *
     * @param QueryBuilderInterface $queryBuilder
     * @return mixed
     */
    public function withQueryBuilder(QueryBuilderInterface $queryBuilder);


    /**
     * Full text search.
     *
     * @param mixed  $storage
     * @param string $terms
     * @param array  $filter
     * @param array  $opts
     * @return Result
     */
    public function search($storage, string $terms, array $filter = [], array $opts = []): Result;
}
