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
     * @ignore
     * @var boolean
     */
    private $i__ghost = false;
    
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
        
        $entity = (new \ReflectionClass($class))->newInstanceWithoutConstructor();
        $props = call_user_func('get_object_vars', $entity); // Get public vars as array
        
        foreach ($props as $prop => $value) {
            unset($entity->$prop);
        }
        
        foreach ($values as $key => $value) {
            $entity->$key = $value;
        }

        $entity->markAsGhost(true);
        
        return $entity;
    }
    
    
    /**
     * Set the ghost state
     * 
     * @param boolean|int $state   true, false or -1
     */
    final protected function markAsGhost($state)
    {
        if (!in_array($state, [true, false, -1])) {
            $var = var_export($state, true);
            throw new \InvalidArgumentException("Ghost state should be true, false or -1, not $var");
        }
        
        $this->i__ghost = $state;
    }
    
    /**
     * Get the ghost state
     * 
     * @return boolean|int
     * @throws \LogicException
     */
    final protected function isMarkedAsGhost()
    {
        if (!isset($this->i__ghost)) {
            throw new \LogicException("Ghost state is null, this shouldn't happen");
        }
        
        return $this->i__ghost;
    }
    
    
    /**
     * Check if the object is a ghost.
     * Returns -1 if the ghost can't be expanded (because it's deleted).
     * 
     * @return boolean|int
     */
    public function isGhost()
    {
        return $this->isMarkedAsGhost();
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
        if ($this->isGhost() !== true) {
            return $this;
        }
        
        $ghostProps = call_user_func('get_object_vars', $this); // Get public vars as array
        
        $this->markAsGhost(-1); // Intermediate state
        
        if (!$this->reload($opts)) {
            return $this;
        }
        
        foreach ($ghostProps as $prop => $value) {
            $this->$prop = $value;
        }

        $this->markAsGhost(false);
        if (method_exists($this, '__construct')) {
            $this->__construct();
        }
        
        return $this;
    }
    
    
    /**
     * Auto-expand entity
     */
    protected function autoExpand()
    {
        if ($this->isGhost() !== true) {
            return;
        }
        
        $this->expand();

        if ($this->isGhost() === -1) {
            $me = get_class($this) . ($this instanceof Identifiable ? ' ' . json_encode($this->getId()) : '');
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
        return $this->isGhost() ? null : $this->$prop;
    }
}
