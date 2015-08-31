<?php

namespace Jasny\DB\Entity;

/**
 * Basic implementation for an entity
 * 
 * @author  Arnold Daniels <arnold@jasny.net>
 * @license https://raw.github.com/jasny/db/master/LICENSE MIT
 * @link    https://jasny.github.com/db
 */
trait Implementation
{
    /**
     * Set the values.
     * 
     * @param array|object $values
     * @return $this
     */
    public function setValues($values)
    {
        // Using closure to prevent setting protected methods
        $set = function($entity) use ($values) {
            foreach ($values as $key=>$value) {
                $entity->$key = $value;
            }
            
            return $entity;
        };
        $set->bindTo(null);
        
        return $set($this);
    }

    /**
     * Get the values.
     * 
     * @param array|object $values
     * @return $this
     */
    public function getValues()
    {
        if ($this instanceof LazyLoadable && $this->isGhost()) $this->expand();
        
        $values = [];
        
        foreach ((array)$this as $key=>$value) {
            if ($key[0] === "\0") continue; // Ignore private and protected properties
            $values[$key] = $value;
        }
        
        return $values;
    }
    
    
    /**
     * Convert loaded values to an entity.
     * Calls the construtor *after* setting the properties.
     * 
     * @param object $values
     * @return static
     */
    public static function fromData($values)
    {
        $class = get_called_class();
        $reflection = new \ReflectionClass($class);
        $entity = $reflection->newInstanceWithoutConstructor();
        
        // Using closure to prevent setting protected methods
        $set = function($entity) use ($values) {
            foreach ($values as $key=>$value) {
                $entity->$key = $value;
            }
            
            return $entity;
        };
        $set->bindTo(null);
        
        $set($entity);
        $entity->__construct();
        
        return $entity;
    }
    
    
    /**
     * Prepare entity for JSON encoding
     * 
     * @return object
     */
    public function jsonSerialize()
    {
        $values = $this->getValues();
        
        foreach ($values as &$value) {
            if ($value instanceof \DateTime) $value = $value->format(\DateTime::ISO8601);
        }
        
        return $this->jsonSerializeFilter((object)$values);
    }
    
    /**
     * Filter object for json serialization
     * 
     * @param object $object
     * @return object
     */
    protected function jsonSerializeFilter($object)
    {
        foreach (get_object_vars($object) as $prop) {
            if ($prop[0] === '_') unset($object->$prop);
        }
        
        return $object;
    }
}
