<?php

namespace Jasny\DB;

/**
 * Interface to a data set, like a DB table (RDMS) or collection (NoSQL).
 * 
 * @author  Arnold Daniels <arnold@jasny.net>
 * @license https://raw.github.com/jasny/db/master/LICENSE MIT
 * @link    https://jasny.github.com/db
 */
interface Dataset
{
    /**
     * Fetch a single entity.
     * 
     * @param mixed|array $id  ID or filter
     * @param array       $opts
     * @return static
     */
    public static function fetch($id, array $opts = []);
    
    /**
     * Check if an exists in the collection.
     * 
     * @param string|array $id  ID or filter
     * @param array        $opts
     * @return boolean
     */
    public static function exists($id, array $opts = []);
    
    /**
     * Fetch all entities from the set.
     * 
     * @param array     $filter
     * @param array     $sort
     * @param int|array $limit   limit or [limit, offset]
     * @param array     $opts
     * @return EntitySet|\Jasny\DB\Entity[]
     */
    public static function fetchAll(array $filter = [], $sort = null, $limit = null, array $opts = []);

    /**
     * Fetch id/description pairs.
     * 
     * @param array     $filter
     * @param array     $sort
     * @param int|array $limit   limit or [limit, offset]
     * @param array     $opts
     * @return array
     */
    public static function fetchPairs(array $filter = [], $sort = null, $limit = null, array $opts = []);
    
    /**
     * Fetch the number of entities in the set.
     * 
     * @param array $filter
     * @param array $opts
     * @return int
     */
    public static function count(array $filter, array $opts = []);
}
