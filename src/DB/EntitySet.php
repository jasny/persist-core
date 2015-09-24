<?php

namespace Jasny\DB;

use Jasny\DB\Entity;

/**
 * An Entity Set is an array of the same entities.
 * 
 * Calling a method on an entity set will call the method on all entities.
 */
class EntitySet implements \IteratorAggregate, \ArrayAccess, \Countable, \JsonSerializable
{
    /**
     * The class name of the entities in this set
     * @var string
     */
    protected $entityClass;
    
    /**
     * @var Entity[]
     */
    protected $entities;
    
    /**
     * Total number of entities.
     * @var int|\Closure
     */
    protected $totalCount;
    
    
    /**
     * Class constructor
     * 
     * <code>
     *   new EntitySet();
     *   new EntitySet('User');
     *   new EntitySet($users);
     *   new EntitySet('User', $users);
     * </code>
     * 
     * @param string             $class  (may be ommitted)
     * @param array|\Traversable $input  Array of entities
     * @param int|\Closure       $total  Total number of entities (if set is limited)
     */
    public function __construct($class = null, $input = [], $total = null)
    {
        if (is_array($class) || $class instanceof \Traversable) {
            list($class, $input, $total) = array_unshift(func_get_args(), null) + [3 => null];
        }

        if (isset($this->entityClass) && !is_a($class, $this->entityClass, true)) {
            throw new \DomainException("A " . self::class . " is only for $this->entityClass entities, not $class");
        }
        $this->entityClass = $class;
        
        $this->entitySetAssertInputArray($input);
        $this->entities = array_values($input);
        
        $this->totalCount = $total;
    }

    
    /**
     * Check if input is valid Entity
     * 
     * @param Entity $entity
     */
    protected function entitySetAssertInput(Entity $entity)
    {
        if (!$entity instanceof Entity) {
            throw new \InvalidArgumentException("Item is not an entity, but a " . get_class($entity));
        }
        
        if (!isset($this->entityClass)) $this->entityClass = get_class($entity);

        if (!is_a($entity, $this->entityClass)) {
            throw new \InvalidArgumentException(get_class($entity) . " is not a $this->entityClass entity");
        }
    }
    
    /**
     * Check if input array contains valid Entities
     * 
     * @param array|\Traversable $input
     */
    protected function entitySetAssertInputArray(&$input)
    {
        if ($input instanceof \Traversable) $input = iterator_to_array($input);
        if (!is_array($input)) {
            $type = is_object($input) ? get_class($input) : gettype($input);
            throw new \InvalidArgumentException("Input should either be an array or Traverable, not a $type");
        }
        
        foreach ($input as $entity) {
            if ($entity instanceof \stdClass || (is_array($entity) && is_string(key($entity)))) {
                if (!isset($this->entityClass)) {
                    throw new \InvalidArgumentException("Unable to cast: entity class not set");
                }
                
                $class = $this->entityClass;
                $entity = $class::fromData($entity);
            }
            
            $this->entitySetAssertInput($entity);
        }
    }
    
    /**
     * Get the class entities of this set (must) have
     * 
     * @return string
     */
    public function getEntityClass()
    {
        if (!isset($this->entityClass)) throw new \Exception("Entity class hasn't been determined yet");
        return $this->entityClass;
    }

    /**
     * Check if index is an integer and not out of bounds.
     * 
     * @param int $index
     */
    protected function entitySetAssertIndex($index)
    {
        if (!is_int($index)) throw new \InvalidArgumentException("Only numeric keys are allowed");
        
        if ($index < 0 || $index > count($this->entities)) {
            throw new \OutOfBoundsException("Index $index is out of bounds");
        }
    }
    
    /**
     * Get the iterator for looping through the set
     * 
     * @return \ArrayIterator
     */
    public function getIterator()
    {
        return new \ArrayIterator($this->entities);
    }

    /**
     * Count the number of entities
     * 
     * @return int
     */
    public function count()
    {
        return count($this->entities);
    }

    /**
     * Count all the entities (if set was limited)
     * 
     * @return int
     */
    public function countTotal()
    {
        if (!$this->totalCount) return $this->count();
        
        if ($this->totalCount instanceof \Closure) {
            $fn = $this->totalCount;
            $this->totalCount = $fn();
        }
        
        return $this->totalCount;
    }
    
    /**
     * Get the entities as array
     * 
     * @return array
     */
    public function getArrayCopy()
    {
        return $this->entities;
    }
    
    /**
     * Find the entity in this set
     * 
     * @param Entity $entity
     * @return Entity|null
     */
    protected function getEntityFromSet(Entity $entity)
    {
        $this->entitySetAssertInput($entity);
        
        $fn = $entity instanceof Entity\Identifiable ? 'getId' : 'toData';
        
        foreach ($this->entities as $cur) {
            if ($cur->$fn() === $entity->$fn()) return $cur;
        }
        
        return null;
    }
    
    /**
     * Check if offset exists or if entity is part of the set
     * 
     * @param int $index
     */
    public function offsetExists($index)
    {
        if ($index instanceof Entity) {
            return (boolean)$this->getEntityFromSet($index);
        }

        if (!is_int($index)) throw new \InvalidArgumentException("Only numeric keys are allowed");
        return isset($this->entities[$index]);
    }

    /**
     * Get the entity of a specific index or find entity in set
     * 
     * @param int|Entity $index
     * @return Entity
     */
    public function offsetGet($index)
    {
        if ($index instanceof Entity) {
            return $this->getEntityFromSet($index);
        }
        
        $this->entitySetAssertIndex($index);
        return $this->entities[$index];
    }

    
    /**
     * Replace the entity of a specific index
     * 
     * @param int    $index
     * @param Entity $entity
     */
    public function offsetSet($index, $entity)
    {
        $this->entitySetAssertInput($entity);
        
        if ($this->offsetExists($entity)) {
            if (isset($index)) unset($this[$index]);
            return;
        }
        
        if (!isset($index)) {
            $this->entities[] = $entity;
        } else{
            $this->entitySetAssertIndex($index);
            $this->entities[$index] = $entity;
        }
    }
    
    /**
     * Remove the entity of a specific index
     * 
     * @param int $index
     */
    public function offsetUnset($index)
    {
        if ($index instanceof Entity) {
            $entity = $this->getEntityFromSet($index);
            if (!isset($entity)) return;
            
            $index = array_search($entity, $this->entities, true);
            if ($index === false) return; // Shouldn't happen
        }
        
        $this->entitySetAssertIndex($index);
        unset($this->entities[$index]);
        $this->entities = array_values($this->entities);
    }
    

    /**
     * Expand all entities and remove permanent ghosts
     * 
     * @param array $opts
     * @return $this
     */
    public function expand(array $opts = [])
    {
        // No lazy loading is nothing to do
        if (!is_a($this->entityClass, Entity\LazyLoading::class, true)) return $this;
        
        foreach ($this->entities as $i => $entity) {
            $entity->expand($opts);
            if ($entity->isGhost()) unset($this->entities[$i]);
        }
        
        $this->entities = array_values($this->entities);
        
        return $this;
    }
    
    /**
     * Prepare for JSON serialization
     * 
     * @return array
     */
    public function jsonSerialize()
    {
        $this->expand();
        return $this->entities;
    }
}
