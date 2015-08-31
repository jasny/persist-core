<?php

namespace Jasny\DB\Entity;

/**
 * Interface for entities that support Lazy loading.
 * 
 * @author  Arnold Daniels <arnold@jasny.net>
 * @license https://raw.github.com/jasny/db/master/LICENSE MIT
 * @link    https://jasny.github.com/db
 */
interface LazyLoading extends \Jasny\DB\Entity
{
    /**
     * Create a ghost object.
     * 
     * @param mixed|array $values  Unique ID or values
     * @return Entity\Ghost
     */
    public static function lazyload($values);
    
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
