<?php

namespace Jasny\DB;

/**
 * Basic implementation for an entity
 * 
 * @author  Arnold Daniels <arnold@jasny.net>
 * @license https://raw.github.com/jasny/db/master/LICENSE MIT
 * @link    https://jasny.github.com/db
 */
trait EntityBasics
{
    /**
     * Set the values.
     * {@interal Using Entity::setValues() shouldn't be any different than setting the properties one by one }}
     * 
     * @param array|object $values
     * @return $this
     */
    final public function setValues($values)
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
     * Convert values to an entity.
     * Calls the construtor after setting the properties.
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
        
        $entity->__construct();
        
        return $entity;
    }
    
    
    /**
     * Cast object to JSON
     * 
     * @return object
     */
    public function jsonSerialize()
    {
        $values = [];
        
        foreach ((array)$this as $key=>$value) {
            if ($key[0] === "\0") continue; // Ignore private and protected properties
            
            if ($value instanceof \DateTime) $value = $value->format(\DateTime::ISO8601);
            
            $values[$key] = $value;
        }
        
        return (object)$values;
    }
}
