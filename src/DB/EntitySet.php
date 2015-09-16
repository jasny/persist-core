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
     * Class constructor
     * 
     * <code>
     *   new EntitySet();
     *   new EntitySet('User');
     *   new EntitySet($users);
     *   new EntitySet('User', $users);
     * </code>
     * 
     * @param string             $entityClass  (may be ommitted)
     * @param array|\Traversable $input        Array of entities
     */
    public function __construct($entityClass = null, $input = [])
    {
        if (is_array($entityClass) || $entityClass instanceof \Traversable) {
            $input = $entityClass;
            $entityClass = null;
        }

        if (isset($this->entityClass) && !is_a($entityClass, $this->entityClass, true)) {
            $class = self::class;
            throw new \DomainException("A $class is only for $this->entityClass entities, not $entityClass entities");
        }
        $this->entityClass = $entityClass;
        
        $this->entitySetAssertInputArray($input);
        $this->entities = $input;
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
            if (!$entity instanceof Entity) throw new \InvalidArgumentException("Not all items are entities");
            
            if (!isset($this->entityClass)) $this->entityClass = get_class($entity);
            
            if (!is_a($entity, $this->entityClass)) {
                throw new \InvalidArgumentException(get_class($entity) . " is not a $this->entityClass entity");
            }
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
     * Call a method on each entity.
     * Return $this if method is a fluid interfaces, otherwise return array of results.
     * 
     * @param string $name
     * @param array  $arguments
     * @return $this|array
     */
    public function __call($name, $arguments)
    {
        $results = [];
        
        foreach ($this as $key => $entity) {
            $results[$key] = call_user_func_array([$entity, $name], $arguments);
        }
        
        return $results === $this->getArrayCopy() ? $this : $results;
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
     * @return arr
     */
    public function count()
    {
        return count($this->entities);
    }

    /**
     * Check if set contains a specific entity
     * 
     * @param Entity $entity
     * @return boolean
     */
    public function contains(Entity $entity)
    {
        $this->entitySetAssertInput($entity);
        
        if ($entity instanceof Entity\Identifiable) {
            foreach ($this->entities as $cur) {
                if ($cur->getId() === $entity->getId()) return true;
            }
        } else {
            foreach ($this->entities as $cur) {
                if ($cur->toData() === $entity->toData()) return true;
            }
        }
        
        return false;
    }
    
    /**
     * Check if offset exists
     * 
     * @param int $index
     */
    public function offsetExists($index)
    {
        if (!is_int($index)) throw new \InvalidArgumentException("Only numeric keys are allowed");
        return isset($this->entities[$index]);
    }

    /**
     * Get the entity of a specific index
     * 
     * @param int|string $index
     * @return Entity
     */
    public function offsetGet($index)
    {
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
        
        if ($this->contains($entity)) {
            if (isset($index)) {
                unset($this->entities[$index]);
                $this->entities[] = array_values($this->entities);
            }
            
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
        $this->entitySetAssertIndex($index);
        unset($this->entities[$index]);
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
        
        $remove = [];
        
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
