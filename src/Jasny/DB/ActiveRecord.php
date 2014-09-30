<?php
namespace Jasny\DB;

/**
 * Interface for the active record design pattern
 * 
 * @author  Arnold Daniels <arnold@jasny.net>
 * @license https://raw.github.com/jasny/db/master/LICENSE MIT
 * @link    https://jasny.github.com/db
 */
interface ActiveRecord
{
    /**
     * Fetch an entity.
     * 
     * @param string|array $filter  ID or filter
     * @return static
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
     * Fetch all entities
     * 
     * @param array $filter
     * @param array $sort
     * @return static[]
     */
    public static function fetchAll(array $filter=[], $sort=null);

    /**
     * Fetch all descriptions.
     * 
     * @param array $filter
     * @param array $sort
     * @return static[]
     */
    public static function fetchList(array $filter=[], $sort=null);
    
    /**
     * Set the values.
     * 
     * @param array|object $values
     * @return $this
     */
    public function setValues($values);

    /**
     * Save the record
     * 
     * @return $this
     */
    public function save();
}
