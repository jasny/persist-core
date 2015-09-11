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
     * <code>
     *   $entity->with(['foo', 'bar']);
     *   $entity->with('foo', 'bar');
     * </code>
     * 
     * @param string|array $properties
     * @param string       ...
     * @return $this
     */
    public function with($property)
    {
        $properties = is_array($property) ? $property : func_get_args();
        
        foreach ($properties as $property) {
            $fn = 'get' . str_replace(' ', '', ucwords(str_replace('_', ' ', $property))); // camelcase
            $this->$property = $this->$fn();
        }
        
        return $this;
    }
}
