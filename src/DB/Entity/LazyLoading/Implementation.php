<?php

namespace Jasny\DB\Entity\LazyLoading;

/**
 * Implementation for LazyLoading interface.
 * 
 * @author  Arnold Daniels <arnold@jasny.net>
 * @license https://raw.github.com/jasny/db/master/LICENSE MIT
 * @link    https://jasny.github.com/db
 */
trait Implementation
{
    /**
     * Flag entity as ghost
     * @var boolean
     */
    protected $ghost__ = false;
    
    /**
     * Create a ghost object.
     * 
     * @param array $values
     * @return static
     */
    public static function lazyload($values)
    {
        $class = get_called_class();
        
        if (is_scalar($values)) {
            if (!is_a($class, Identifiable::class)) {
                throw new Exception("Unable to lazy load a scalar value for $class: Identity property not defined");
            }
            
            $prop = static::getIdProperty();
            if (is_array($prop)) {
                throw new Exception("Unable to lazy load a scalar value for $class: Class has a complex identity");
            }
            
            $values = [$prop => $values];
        }
        
        $reflection = new \ReflectionClass($class);
        $entity = $reflection->newInstanceWithoutConstructor();
        
        foreach ((array)$entity as $prop => $value) {
            if ($prop[0] === "\0") continue; // Ignore private and protected properties
            unset($entity->$prop);
        }
        
        foreach ($values as $key => $value) {
            $entity->$key = $value;
        }

        $entity->ghost__ = true;
        
        return $entity;
    }
    
    /**
     * Check if the object is a ghost.
     * 
     * @return boolean
     */
    public function isGhost()
    {
        return $this->ghost__;
    }
    
    /**
     * Expand a ghost.
     * Does nothing is entity isn't a ghost.
     * 
     * @return $this
     */
    public function expand()
    {
        if ($this->isGhost()) {
            $ghostProps = get_object_vars($this);
            
            $this->reload();
            
            foreach ($ghostProps as $prop => $value) {
                $this->$prop = $value;
            }

            $this->ghost__ = false;
            if (method_exists($this, '__construct')) $this->__construct();
        }
        
        return $this;
    }
    
    /**
     * Auto-expand ghost
     * 
     * @param string $prop  Property name
     * @return mixed
     */
    public function __get($prop)
    {
        return $this->expand()->$prop;
    }
}
