<?php

declare(strict_types=1);

namespace Jasny\DB\Search;

use Jasny\DB\CRUD\Result;

/**
 * Service for full text search.
 */
interface SearchInterface
{
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
