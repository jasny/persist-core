<?php

declare(strict_types=1);

namespace Jasny\DB\Search;

use Jasny\DB\PrototypeInterface;
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
    public function search($terms, array $filter = [], array $opts = []): EntityCollectionInterface;
}
