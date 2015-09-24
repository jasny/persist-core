<?php

namespace Jasny\DB\Dataset;

/**
 * Dataset supports full text search
 */
interface Search
{
    /**
     * Search entities.
     * 
     * @param string    $terms
     * @param array     $filter
     * @param array     $sort
     * @param int|array $limit
     * @param array     $opts
     * @return Jasny\DB\EntitySet|Jasny\DB\Entity[]
     */
    public static function search($terms, $filter, $sort = null, $limit = null, array $opts = []);
}
