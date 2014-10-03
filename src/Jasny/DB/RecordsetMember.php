<?php

namespace Jasny\DB;

/**
 * Indicates that an entity is part of a recordset, like a table or collection.
 */
interface RecordsetMember
{
    /**
     * Fetch a single entity.
     * 
     * @param mixed|array $filter  ID or filter
     * @return static
     */
    public static function fetch($filter);
    
    /**
     * Check if a record exists in the set.
     * 
     * @param mixed|array $filter  ID or filter
     * @return boolean
     */
    public static function exists($filter);
    
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
     * @return static[]
     */
    public static function count(array $filter);
}
