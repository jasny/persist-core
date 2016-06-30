<?php

namespace Jasny\DB\Entity\Redactable;

use stdClass;
use Jasny\DB\Entity\Enrichable;
use Jasny\Meta\Introspection;

/**
 * Entity can be enriched with related data
 */
trait Implementation
{
    /**
     * @var boolean
     * @ignore
     */
    private $i__censor_default = false;
    
    /**
     * @var array
     * @ignore
     */
    private $i__censored = [];
    
    
    /**
     * Set if properties are censored by default
     * 
     * @param boolean $censored
     */
    final protected function censorByDefault($censored)
    {
        $this->i__censor_default = $censored;
    }
    
    /**
     * Get if properties are censored by default
     * 
     * @return boolean
     */
    final protected function isCensoredByDefault()
    {
        return $this->i__censor_default;
    }
    
    /**
     * Set a censored property
     * 
     * @param string $property
     * @param boolean $censored
     */
    final protected function markAsCensored($property, $censored)
    {
        $this->i__censored[$property] = $censored;
    }

    /**
     * Check if a property is marked as censored
     * 
     * @param string  $property
     * @return boolean
     */
    final protected function hasMarkedAsCensored($property)
    {
        return isset($this->i__censored[$property]) ? $this->i__censored[$property] : null;
    }
    
    /**
     * Remove all properties as censored list
     */
    final protected function resetMarkedAsCensored()
    {
        $this->i__censor_default = false;
        $this->i__censored = [];
    }
    
    /**
     * Get all properties that are marked as censored
     */
    final protected function getAllMarkedAsCensored()
    {
       return $this->i__censored; 
    }
    
    
    /**
     * Check if the property is censored for this Entity
     * 
     * @param string $property
     * @return boolean
     */
    public function hasCensored($property)
    {
        $censored = $this->hasMarkedAsCensored($property);
        
        if (!isset($censored) && $this instanceof Introspection) {
            $censored = static::meta()->ofProperty($property)->censor;
        }
        
        return (boolean)$censored;
    }
    

    /**
     * Censor properties from entity.
     * 
     * @param string[] $properties
     * @return static
     */
    public function without(...$properties)
    {
        if (is_array($properties) && count($properties) === 1 && is_array($properties[0])) {
            $properties = $properties[0]; // BC v2.2
        }
        
        foreach ((array)$properties as $property) {
            $this->markAsCensored($property, true);
        }
        
        return $this;
    }
    
    /**
     * Censor all  only the specified properties.
     * Enriches with related data if needed.
     * 
     * @param string[] $properties
     * @return static
     */
    public function withOnly(...$properties)
    {
        if (is_array($properties) && count($properties) === 1 && is_array($properties[0])) {
            $properties = $properties[0]; // BC v2.2
        }

        $this->censorByDefault(true);
        
        if ($this instanceof Enrichable) {
            $this->with($properties);
        }
    }

    
    /**
     * Filter object for json serialization
     * 
     * @param stdClass $object
     * @return stdClass
     */
    protected function jsonSerializeFilterCensored(stdClass $object)
    {
        foreach ($object as $property => $value) {
            $censored = $this->hasMarkedAsCensored($property);
            
            if (!isset($censored)) {
                $censored = $this->isCensoredByDefault();
            }
            
            if ($censored) {
                unset($object->$property);
            }
        }
        
        return $object;
    }
}
