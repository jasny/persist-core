<?php

declare(strict_types=1);

namespace Jasny\DB\Search;

use Jasny\EntityCollectionInterface;
use Jasny\EntityInterface;
use Jasny\IteratorPipeline\Pipeline;

/**
 * Gateway support for full text search.
 */
interface SearchInterface
{
    /**
     * Find records in the data source using full text search.
     * 
     * @param string $terms
     * @param array  $filter
     * @param array  $opts
     * @return Pipeline
     */
    public function search(string $terms, array $filter = [], array $opts = []): Pipeline;
}
