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
     * @param mixed|array $filter  ID or filter
     * @return static
     */
    public static function fetch($filter);
    
    /**
     * Fetch all entities from the set.
     * 
     * @param array $filter
     * @param array $sort
     * @return static[]
     */
    public static function fetchAll(array $filter=[], $sort=null);

    /**
     * Fetch all descriptions from the set.
     * 
     * @param array $filter
     * @param array $sort
     * @return static[]
     */
    public static function fetchList(array $filter=[], $sort=null);
    
    /**
     * Fetch the number of entities in the set.
     * 
     * @param array $filter
     * @return int
     */
    public static function count(array $filter);
}
