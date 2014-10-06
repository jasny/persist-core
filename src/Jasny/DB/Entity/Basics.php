<?php

namespace Jasny\DB\Entity;

use \Jasny\Meta\TypedObject;

/**
 * Basic implementation for an entity
 * 
 * @author  Arnold Daniels <arnold@jasny.net>
 * @license https://raw.github.com/jasny/db/master/LICENSE MIT
 * @link    https://jasny.github.com/db
 */
trait Basics
{
    /**
     * Set the values.
     * {@interal Using Entity::setValues() shouldn't be any different than setting the properties one by one }}
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
     * {@interal Using Entity::getValues() shouldn't be any different than getting the properties one by one }}
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
    public static function __set_state($values)
    {
        $class = get_called_class();
        $reflection = new \ReflectionClass($class);
        $entity = $reflection->newInstanceWithoutConstructor();
        
        foreach ($values as $key=>$value) {
            $entity->$key = $value;
        }
        
        if ($entity instanceof TypedObject) $entity->cast();
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
        
        return (object)$values;
    }
}
