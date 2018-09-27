<?php

declare(strict_types=1);

namespace Jasny\DB\Search;

use Jasny\DB\SearchInterface;
use Jasny\EntityCollectionInterface;
use Jasny\EntityInterface;
use Jasny\IteratorPipeline\Pipeline;

/**
 * Search is not supported
 */
class NoSearch implements SearchInterface
{
    /**
     * SearchInterface entities.
     *
     * @param string $terms
     * @param array  $filter
     * @param array  $opts
     * @return Pipeline
     */
    public function search(string $terms, array $filter = [], array $opts = []): Pipeline
    {
        throw new \BadMethodCallException("Search is not supported");
    }
}
