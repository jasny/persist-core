<?php

namespace Jasny\DB\Entity;

/**
 * Triat for lazy loading through ghost objects.
 * 
 * @author  Arnold Daniels <arnold@jasny.net>
 * @license https://raw.github.com/jasny/db/master/LICENSE MIT
 * @link    https://jasny.github.com/db
 */
trait LazyLoading
{
    /**
     * Whether object is a ghost.
     * @var boolean
     */
    private $ghost__ = false;
    
    
    /**
     * Create a ghost object.
     * 
     * @param array $values
     * @return static
     */
    public static function ghost($values)
    {
        $class = get_called_class();
        $reflection = new \ReflectionClass($class);
        $entity = $reflection->newInstanceWithoutConstructor();
        
        $entity->ghost__ = true;
        
        foreach ((array)$entity as $prop => $value) {
            if ($prop[0] === "\0") continue; // Ignore private and protected properties
            unset($entity->$prop);
        }
        
        foreach ($values as $key => $value) {
            $entity->$key = $value;
        }
        
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
            if ($this instanceof ActiveRecord) {
                $entity = static::fetch($this->getId());
            } elseif (is_a(get_called_class() . 'Mapper', 'Jasny\DB\DataMapper', true)) {
                $class = get_called_class() . 'Mapper';
                $entity = $class::fetch($this->getId());
            } else {
                throw new \Exception("Don't know how to fetch a " . get_called_class());
            }
            
            foreach ($entity as $prop => $value) {
                if ($prop[0] === "\0") continue; // Ignore private and protected properties
                
                if (!property_exists($this, $prop)) $this->$prop = $value;
            }
            
            $this->__construct();
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
        if ($this->isGhost()) $this->expand();
        return $this->$prop;
    }
}
