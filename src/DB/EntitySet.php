<?php

namespace Jasny\DB;

use Jasny\DB\Entity;
use Jasny\Meta\Introspection;

/**
 * An Entity Set is an array of the same entities.
 * 
 * Calling a method on an entity set will call the method on all entities.
 */
class EntitySet implements \IteratorAggregate, \ArrayAccess, \Countable, \JsonSerializable
{
    /**
     * Flag to allow duplicate entities
     */
    const ALLOW_DUPLICATES = 0b1;

    /**
     * Flag to preserve associated keys
     */
    const PRESERVE_KEYS = 0b10;
    
    
    /**
     * Control the behaviour of the entity set
     * @var int
     */
    protected $flags = 0;
    
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
     * @param string                  $class     (may be ommitted)
     * @param Entities[]|\Traversable $entities  Array of entities
     * @param int|\Closure            $total     Total number of entities (if set is limited)
     * @param int                     $flags     Control the behaviour of the entity set
     */
    public function __construct($class = null, $entities = [], $total = null, $flags = 0)
    {
        if (is_array($class) || $class instanceof \Traversable) {
            list($class, $entities, $total, $flags) = array_unshift(func_get_args(), null) + [3 => null, 4 => 0];
        }

        if (
            isset($this->entityClass) &&
            isset($class) &&
            $class !== $this->entityClass &&
            !is_a($class, $this->entityClass, true)
        ) {
            throw new \DomainException("A " . static::class . " is only for $this->entityClass entities, not $class");
        }
        
        $this->flags = $flags;
        $this->entityClass = $class;
        $this->setEntities($entities);
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
    protected function entitySetAssertInputArray($input)
    {
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
     * Get the flags set for this entity set
     *
     * @return int
     */
    public function getFlags()
    {
        return $this->flags;
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
     * Set the entities
     * 
     * @param array|\Traversable $entities
     */
    protected function setEntities($entities)
    {
        if ($entities instanceof \Traversable) $entities = iterator_to_array($entities);
        $this->entitySetAssertInputArray($entities);
        
        if (~$this->flags & self::PRESERVE_KEYS) $entities = array_values($entities);
        if (~$this->flags & self::ALLOW_DUPLICATES) $entities = $this->uniqueEntities($entities);
        
        $this->entities = $entities;
    }
    
    /**
     * Remove duplicate entities
     * 
     * @param array|\Traversable $input  Array of entities
     */
    protected function uniqueEntities($input)
    {
        $ids = [];
        $entities = [];
        
        foreach ($input as $entity) {
            $id = $entity instanceof Entity\Identifiable ? $entity->getId() : $entity->toData();
            if (isset($id) && array_search($id, $ids)) continue;
            
            $ids[] = $id;
            $entities[] = $entity;
        }
        
        return $entities;
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
     * Get the entities as array
     * 
     * @return array
     */
    public function getArrayCopy()
    {
        return $this->entities;
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
     * Check if the entity exists in this set
     * 
     * @param mixed|Entity $id
     * @return boolean
     */
    public function contains($id)
    {
        return (boolean)$this->get($id);
    }
    
    /**
     * Find the entity in this set
     * 
     * @param mixed|Entity $id
     * @return Entity|null
     */
    public function get($id)
    {
        $fn = is_a($this->entityClass, Entity\Identifiable::class, true) ? 'getId' : 'toData';
        
        if ($id instanceof Entity) {
            $this->entitySetAssertInput($id);
            $id = $id->$fn();
        }
        
        if (!isset($id)) return null;
        
        foreach ($this->entities as $entity) {
            if ($entity->$fn() === $id) return $entity;
        }
        
        return null;
    }
    
    /**
     * Add an entity to the set
     * 
     * @param Entity $entity
     */
    final public function add($entity)
    {
        $this->offsetSet(null, $entity); 
    }
    
    /**
     * Remove an entity from the set
     * 
     * @param mixed|Entity $id
     */
    public function remove($id)
    {
        do {
            $entity = $this->get($id);
            if ($entity) {
                unset($this->entities[array_search($entity, $this->entities, true)]);
            }
        } while ($this->flags & self::ALLOW_DUPLICATES && $entity);
        
        if (~$this->flags & self::PRESERVE_KEYS) {
            $this->entities = array_values($this->entities);
        }
    }
    
    /**
     * Return a unique set of entities.
     * Return this entity set, if it's already unique.
     * 
     * @return static|$this
     */
    public function unique()
    {
        if (~$this->flags & self::ALLOW_DUPLICATES) return $this;
        
        $flags = $this->flags & ~static::ALLOW_DUPLICATES;
        return new static($this->getEntityClass(), $this->entitiesm, $this->totalCount, $flags);
    }
    
    
    /**
     * Check if offset exists or if entity is part of the set
     * 
     * @param int $index
     */
    public function offsetExists($index)
    {
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
        
        if (~$this->flags & self::ALLOW_DUPLICATES && $this->contains($entity)) {
            if (isset($index)) $this->offsetUnset($index);
            return;
        }

        if (!isset($index)) {
            $this->entities[] = $entity;
        } else {
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
        
        if (~$this->flags & self::PRESERVE_KEYS) {
            $this->entities = array_values($this->entities);
        }
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
        
        return $results === $this->entities ? $this : $results;
    }
    
    /**
     * Get property of entities as array.
     * If property is an array or EntitySet, it will be flattened.
     * 
     * @param string $property
     * @return EntitySet|array
     */
    public function __get($property)
    {
        if (is_a($this->entityClass, Introspection::class, true)) {
            $entityClass = $this->entityClass;
            $var = $entityClass::meta()->of($property)['var'];
            if ($var) $var = substr_replace('[]', '', $var);
        }
        
        $array = [];
        
        foreach ($this->entities as $entity) {
            if (!isset($entity->$property)) continue;
            
            $value = $entity->$property;
            
            if ($value instanceof self) {
                if (!$var) $var = $value->entityClass;
                $value = $value->getArrayCopy();
            }
            
            if (!isset($var)) $var = is_object($value) ? get_class($value) : gettype($value);
            
            if (is_array($value)) {
                $array = array_merge($array, $value);
            } else {
                $array[] = $value;
            }
        }
        
        return is_a($var, Entity::class, true) 
            ? $var::entitySet($array, null, self::ALLOW_DUPLICATES)
            : $array;
    }
}
