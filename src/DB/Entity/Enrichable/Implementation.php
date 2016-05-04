<?php

namespace Jasny\DB\Entity\Enrichable;

use Jasny\DB\Entity\LazyLoading;
use Jasny\DB\EntitySet;
use Jasny\DB\Entity\Redactable;

/**
 * Entity can be enriched with related data
 */
trait Implementation 
{
    use Redactable\Implementation;
    
    /**
     * Enrich entity with related data.
     * Returns a clone of $this with the additional data.
     * 
     * @param string[] $properties
     * @return $this
     */
    public function with(...$properties)
    {
        if (is_array($properties) && count($properties) === 1 && is_array($properties[0])) {
            $properties = $properties[0]; // BC v2.2
        }
        
        $uncensor = $this instanceof Redactable && method_exists($this, 'markAsCensored');
        
        foreach ((array)$properties as $property) {
            if (!isset($this->$property)) {
                $fn = 'get' . str_replace(' ', '', ucwords(str_replace('_', ' ', $property))); // camelcase
                
                if (is_callable([$this, $fn])) {
                    $this->$property = $this->$fn();
                }
            }
            
            if ($this->$property instanceof LazyLoading || $this->$property instanceof EntitySet) {
                $this->$property->expand();
            }
            
            if ($uncensor) {
                $this->markAsCensored($property, false);
            }
        }
        
        return $this;
    }
}
