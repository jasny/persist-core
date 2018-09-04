<?php

namespace Jasny\DB;

use Jasny\EntityCollectionInterface;
use Jasny\EntityInterface;

/**
 * Gateway support for full text search
 */
interface SearchInterface
{
    /**
     * SearchInterface entities.
     * 
     * @param string $terms
     * @param array  $filter
     * @param array  $opts
     * @return EntityCollectionInterface|EntityInterface[]
     */
    public static function search($terms, array $filter = [], array $opts = []): EntityCollectionInterface;
}
