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
     * Fetch a single entity.
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
