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
     * @param array        $opts
     * @return Entity
     */
    public static function fetch($filter, array $opts = []);
    
    /**
     * Check if an entity exists
     * 
     * @param string|array $filter  ID or filter
     * @param array        $opts
     * @return boolean
     */
    public static function exists($filter, array $opts = []);
    
    /**
     * Save the entity
     * 
     * @param Entity $entity
     * @param array  $opts
     */
    public static function save(Entity $entity, array $opts = []);

    /**
     * Delete the entity
     * 
     * @param Entity $entity
     * @param array  $opts
     */
    public static function delete(Entity $entity, array $opts = []);
}
