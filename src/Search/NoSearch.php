<?php

declare(strict_types=1);

namespace Jasny\DB\Search;

use Jasny\DB\SearchInterface;
use Jasny\EntityCollectionInterface;
use Jasny\EntityInterface;

/**
 * Search is not supported
 */
class NoSearch implements SearchInterface
{
    /**
     * SearchInterface entities.
     *
     * @param string $terms
     * @param array $filter
     * @param array $opts
     * @return EntityCollectionInterface|EntityInterface[]
     */
    public static function search($terms, array $filter = [], array $opts = []): EntityCollectionInterface
    {
        throw new \BadMethodCallException("Search is not supported");
    }
}