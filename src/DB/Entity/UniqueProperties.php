<?php

namespace Jasny\DB\Entity;

/**
 * Identify that a property is unique in a a collection
 */
interface UniqueProperties
{
    /**
     * Check no other entities with the same value of the property exists in the recordset
     * 
     * @param string       $property
     * @param string|array $group     List of properties that should match
     * @return boolean
     */
    public function hasUnique($property, $group = null);
}
