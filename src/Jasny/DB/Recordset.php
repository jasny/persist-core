<?php

namespace Jasny\DB;

/**
 * Indicates that an entity is part of a set, like a table or collection.
 */
interface Recordset
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
