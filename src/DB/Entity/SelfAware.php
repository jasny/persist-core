<?php

namespace Jasny\DB\Entity;

/**
 * Entity know where it's stored and how it can be loaded
 */
interface SelfAware
{
    /**
     * Reload the entity
     * 
     * @param array $opts
     * @return $this
     */
    public function reload(array $opts = []);
    
    /**
     * Check no other entities with the same value of the property exists in the recordset
     * 
     * @param string       $property
     * @param string|array $group     List of properties that should match
     * @param array        $opts
     * @return boolean
     */
    public function hasUnique($property, $group = null, array $opts = []);
}
