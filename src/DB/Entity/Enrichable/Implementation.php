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
     * @param string|array $property
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

    /**
     * Unset properties from entity
     * 
     * <code>
     *   $entity->without(['foo', 'bar']);
     *   $entity->without('foo', 'bar');
     * </code>
     * 
     * @param string|array $property
     * @param string       ...
     * @return $this
     */
    public function without($property)
    {
        $properties = is_array($property) ? $property : func_get_args();
        $myProps = array_keys((array)$this); // This adds \0 for private properties
        
        foreach ($properties as $property) {
            if ($property[0] === "\0") continue; // Ignore private properties
            if (array_search($property, $myProps)) unset($this->$property);
        }
        
        return $this;
    }
}
