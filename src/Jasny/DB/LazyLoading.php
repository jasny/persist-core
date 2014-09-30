<?php

namespace Jasny\DB;

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
     * @param mixed|array $values  Unique ID or values
     * @return static
     */
    public static function ghost($values)
    {
        $class = get_called_class();
        $reflection = new \ReflectionClass($class);
        $entity = $reflection->newInstanceWithoutConstructor();
        
        $entity->ghost__ = true;
        
        foreach ($entity as $prop => $value) {
            if ($prop[0] === "\0") continue; // Ignore private and protected properties
            
            if (array_key_exists($prop, $values)) {
                $entity->$prop = $value;
            } else {
                unset($entity->$prop);
            }
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
            $entity = static::fetch($this->getId());
            
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
