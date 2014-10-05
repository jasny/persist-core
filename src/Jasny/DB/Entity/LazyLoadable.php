<?php

namespace Jasny\DB\Entity;

/**
 * Interface for entities that support lazy loading through ghost objects.
 * 
 * @author  Arnold Daniels <arnold@jasny.net>
 * @license https://raw.github.com/jasny/db/master/LICENSE MIT
 * @link    https://jasny.github.com/db
 */
interface LazyLoadable
{
    /**
     * Create a ghost object.
     * 
     * @param mixed|array $values  Unique ID or values
     * @return static
     */
    public static function ghost($values);
    
    /**
     * Check if the object is a ghost.
     * 
     * @return boolean
     */
    public function isGhost();
    
    /**
     * Expand a ghost.
     * Does nothing is entity isn't a ghost.
     * 
     * @return $this
     */
    public function expand();
}
