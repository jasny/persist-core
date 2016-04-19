<?php

namespace Jasny\DB\Entity\Enrichable;

use Jasny\DB\Entity\LazyLoading;
use Jasny\DB\Entity\EntitySet;

/**
 * Entity can be enriched with related data
 */
trait Implementation
{
    /**
     * Enrich entity with related data.
     * Returns a clone of $this with the additional data.
     * 
     * @param string[] $properties
     * @return $this
     */
    public function with(...$properties)
    {
        if (count($properties) === 1 && is_array($properties)) $properties = $properties[0]; // BC v2.2
        
        foreach ($properties as $property) {
            if (!isset($this->$property)) {
                $fn = 'get' . str_replace(' ', '', ucwords(str_replace('_', ' ', $property))); // camelcase
                if (is_callable([$this, $fn])) $this->$property = $this->$fn();
            }
            
            if ($this->$property instanceof LazyLoading || $this->$property instanceof EntitySet) {
                $this->$property->expand();
            }
        }
        
        return $this;
    }

    /**
     * Remove properties from entity.
     * Returns a clone of $this without the specified properties.
     * 
     * @param string[] $properties
     * @return static
     */
    public function without(...$properties)
    {
        if (count($properties) === 1 && is_array($properties)) $properties = $properties[0]; // BC v2.2
        
        $myProps = array_keys((array)$this); // This adds \0 for private properties
        
        foreach ($properties as $property) {
            if ($property[0] === "\0") continue; // Ignore private properties
            if (in_array($property, $myProps)) unset($this->$property);
        }
        
        return $this;
    }
    
    /**
     * Remove properties from entity.
     * Returns a clone of $this without the specified properties.
     * 
     * @param string[] $properties
     * @return static
     */
    public function withOnly(...$properties)
    {
        if (count($properties) === 1 && is_array($properties)) $properties = $properties[0]; // BC v2.2
        
        $myProps = array_keys((array)$this); // This adds \0 for private properties
        
        foreach ($myProps as $property) {
            if ($property[0] === "\0") continue; // Ignore private properties
            if (!in_array($property, $properties)) unset($this->$property);
        }
        
        return $this->with(...$properties);
    }
}
