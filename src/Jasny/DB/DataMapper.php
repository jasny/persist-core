<?php

namespace Jasny\DB;

/**
 * A Data Mapper is a bridge between the entity and the database
 * 
 * @author  Arnold Daniels <arnold@jasny.net>
 * @license https://raw.github.com/jasny/db/master/LICENSE MIT
 * @link    https://jasny.github.com/db
 */
interface DataMapper
{
    /**
     * Fetch a single entity.
     * 
     * @param string|array $filter  ID or filter
     * @return Entity
     */
    public static function fetch($filter);
    
    /**
     * Check if an entity exists
     * 
     * @param string|array $filter  ID or filter
     * @return boolean
     */
    public static function exists($filter);
    
    /**
     * Save the entity
     * 
     * @param Entity $entity
     */
    public static function save(Entity $entity);

    /**
     * Delete the entity
     * 
     * @param Entity $entity
     */
    public static function delete();
}
