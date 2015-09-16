<?php

namespace Jasny\DB\Entity\LazyLoading;

use Jasny\DB\Entity\Identifiable;

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
     * Reload the entity
     * 
     * @param array $opts
     */
    abstract public function reload(array $opts = []);
    
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
            if (!is_a($class, Identifiable::class, true)) {
                throw new \Exception("Unable to lazy load a scalar value for $class: Identity property not defined");
            }
            
            $prop = static::getIdProperty();
            if (is_array($prop)) {
                throw new \Exception("Unable to lazy load a scalar value for $class: Class has a complex identity");
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
     * Returns -1 if the ghost can't be expanded (because it's deleted).
     * 
     * @return boolean|int
     */
    public function isGhost()
    {
        return $this->ghost__;
    }
    
    /**
     * Expand a ghost.
     * Does nothing is entity isn't a ghost or can't be expanded.
     * 
     * @param array $opts
     * @return $this
     */
    public function expand(array $opts = [])
    {
        if ($this->ghost__ !== true) return $this;
        
        $ghostProps = get_object_vars($this);
        
        $this->ghost__ = -1; // Intermediate state
        
        if (!$this->reload($opts)) return $this;
        
        foreach ($ghostProps as $prop => $value) {
            $this->$prop = $value;
        }

        $this->ghost__ = false;
        if (method_exists($this, '__construct')) $this->__construct();
        
        return $this;
    }
    
    
    /**
     * Auto-expand entity
     */
    protected function autoExpand()
    {
        if ($this->ghost__ !== true) return;
        
        $this->expand();

        if ($this->ghost__ === -1) {
            $me = get_class($this) . ($this instanceof Identifiable ? ' ' . $this->getId() : '');
            trigger_error("Unable to auto-expand $me", E_USER_NOTICE);
        }
    }
    
    /**
     * Auto-expand ghost and return property
     * 
     * @param string $prop  Property name
     * @return mixed
     */
    public function __get($prop)
    {
        $this->autoExpand();
        return $this->ghost__ ? null : $this->$prop;
    }
}
