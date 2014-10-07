<?php

namespace Jasny\DB\Entity;

/**
 * Interface for the active record design pattern
 * 
 * @author  Arnold Daniels <arnold@jasny.net>
 * @license https://raw.github.com/jasny/db/master/LICENSE MIT
 * @link    https://jasny.github.com/db
 */
interface ActiveRecord extends \Jasny\DB\Entity
{
    /**
     * Fetch a single entity.
     * 
     * @param string|array $filter  ID or filter
     * @return static
     */
    public static function fetch($filter);
    
    /**
     * Check if an entity exists in the database.
     * 
     * @param string|array $filter  ID or filter
     * @return boolean
     */
    public static function exists($filter);

    /**
     * Save the entity to the database.
     * 
     * @return $this
     */
    public function save();

    /**
     * Delete the entity from the database.
     * 
     * @return $this
     */
    public function delete();
}
