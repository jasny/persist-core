<?php

namespace Jasny\DB\Entity\Enrichable;

/**
 * Entity can be enriched with related data
 */
trait Implementation
{
    /**
     * Enrich entity with related data
     * 
     * @param string|array $properties
     * @return $this
     */
    public function with($properties)
    {
        foreach ((array)$properties as $property) {
            $fn = 'get' . str_replace(' ', '', ucwords(str_replace('_', ' ', $property))); // camelcase
            $this->property = $this->$fn();
        }
        
        return $this;
    }
}
