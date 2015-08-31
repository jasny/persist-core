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
     * @return static
     */
    public static function fetch($id);
    
    /**
     * Check if an exists in the collection.
     * 
     * @param string|array $id  ID or filter
     * @return boolean
     */
    public static function exists($id);
    
    /**
     * Fetch all entities from the set.
     * 
     * @param array     $filter
     * @param array     $sort
     * @param int|array $limit   limit or [limit, offset]
     * @return static[]
     */
    public static function fetchAll(array $filter = [], $sort = null, $limit = null);

    /**
     * Fetch all descriptions from the set.
     * 
     * @param array $filter
     * @param array $sort
     * @param int|array $limit   limit or [limit, offset]
     * @return array
     */
    public static function fetchList(array $filter = [], $sort = null, $limit = null);
    
    /**
     * Fetch the number of entities in the set.
     * 
     * @param array $filter
     * @return int
     */
    public static function count(array $filter);
}
