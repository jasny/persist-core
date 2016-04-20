<?php

namespace Jasny\DB;

use Jasny\DB\Entity;
use Jasny\DB\Data;
use Jasny\Meta\Introspection;

/**
 * An Entity Set is an array of the same entities.
 * 
 * Calling a method on an entity set will call the method on all entities.
 */
class EntitySet implements \IteratorAggregate, \ArrayAccess, \Countable, \JsonSerializable, Data
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
     * Flag to indicate to use lazyload (if available) when casting
     */
    const LAZYLOAD = 0b100;
    
    
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
     * @param Entities[]|\Traversable $entities  Array of entities
     * @param int|\Closure            $total     Total number of entities (if set is limited)
     * @param int                     $flags     Control the behaviour of the entity set
     */
    public function __construct($entities = [], $total = null, $flags = 0)
    {
        list($class, $entities, $total, $flags) = $this->getConstructArgs(func_get_args()); // BC v2.2
        
        $this->flags |= $flags;
        if (isset($class)) $this->setEntityClass($class);
        $this->setEntities($entities);
        $this->totalCount = $total;
    }

    /**
     * Shift the constructor arguments if $class is omitted.
     * 
     * @param array $args
     * @return array  [class, entities, total, flags, ...]
     */
    protected function getConstructArgs(array $args)
    {
        if (isset($args[0]) && (is_array($args[0]) || $args[0] instanceof \Traversable)) {
            array_unshift($args, null);
        }
        
        return $args + [null, [], null, 0, null, null, null, null, null, null, null, null, null, null];
    }

    /**
     * Factory method
     * 
     * @param string                  $class     Class name
     * @param Entities[]|\Traversable $entities  Array of entities
     * @param int|\Closure            $total     Total number of entities (if set is limited)
     * @param int                     $flags     Control the behaviour of the entity set
     */
    public function forClass($class, $entities = [], $total = null, $flags = 0)
    {
        $refl = new \ReflectionClass($class);
        
        $entitySet = $refl->newInstanceWithoutConstructor();
        $entitySet->entityClass = $class;
        
        $args = func_get_args();
        $entitySet->__construct(...$args);
        
        return $entitySet;
    }

    
    /**
     * Set the entity class
     * 
     * @param string $class
     */
    protected function setEntityClass($class)
    {
        if (!is_a($class, Entity::class, true)) {
            throw new \DomainException("A $class is not an Entity");
        }
        
        if (
            isset($this->entityClass) &&
            isset($class) &&
            $class !== $this->entityClass &&
            !is_a($class, $this->entityClass, true)
        ) {
            $setClass = get_class($this);
            throw new \DomainException("A $setClass is only for {$this->entityClass} entities, not $class");
        }
        
        if (!empty($this->entities) && $class !== $this->entityClass) {
            throw new \LogicException("Can't change the entity class to '$class': set already contains entities");
        }
        
        $this->entityClass = $class;
    }
    
    /**
     * Check if input is valid Entity
     * 
     * @param Entity $entity
     */
    protected function assertEntity(Entity $entity)
    {
        if (!isset($this->entityClass)) {
            $this->setEntityClass(get_class($entity));
        }
        
        if (!is_a($entity, $this->entityClass)) {
            throw new \InvalidArgumentException(get_class($entity) . " is not a {$this->entityClass} entity");
        }
    }
    
    /**
     * Check if input array contains valid Entities
     * 
     * @param array|\Traversable $entities
     * @return array
     */
    protected function castEntities($entities)
    {
        if (!is_array($entities) && !$entities instanceof \Traversable) {
            $type = (is_object($entities) ? get_class($entities) . ' ' : '') . gettype($entities);
            throw new \InvalidArgumentException("Input should either be an array or Traverable, not a $type");
        }
        
        if ($entities instanceof \Traversable) $entities = iterator_to_array($entities);
        
        foreach ($entities as &$entity) {
            if ($entity instanceof \stdClass || (is_array($entity) && is_string(key($entity)))) {
                if (!isset($this->entityClass)) {
                    throw new \InvalidArgumentException("Unable to cast: entity class not set");
                }
                
                $class = $this->entityClass;
                
                $entity = $this->flags & static::LAZYLOAD && is_a($class, Entity\LazyLoading::class, true)
                    ? $class::lazyload($entity)
                    : $class::fromData($entity);
            }
            
            $this->assertEntity($entity);
        }
        
        return $entities;
    }

    /**
     * Check if index is an integer and not out of bounds.
     * 
     * @param int     $index
     * @param boolean $add     Indexed is used for adding an element
     */
    protected function assertIndex($index, $add = false)
    {
        if ($this->flags & self::PRESERVE_KEYS) {
            if (!isset($index)) throw new \LogicException("Cannot use [], please use an index");
            
            if (!$add && !isset($this->entities[$index])) {
                throw new \OutOfBoundsException("Set doesn't contain a '$index' entity");
            }
            
            return;
        }
        
        if (!is_int($index)) throw new \InvalidArgumentException("Only numeric keys are allowed");
        
        if ($index < 0 || $index > count($this->entities) - ($add ? 0 : 1)) {
            throw new \OutOfBoundsException("Index '$index' is out of bounds");
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
     * @param array|\Traversable $input
     */
    protected function setEntities($input)
    {
        $entities = $this->castEntities($input);
        
        if (~$this->flags & self::ALLOW_DUPLICATES) $entities = $this->uniqueEntities($entities);
        if (~$this->flags & self::PRESERVE_KEYS) $entities = array_values($entities);
        
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
        
        foreach ($input as $key => $entity) {
            $id = $entity instanceof Entity\Identifiable ? $entity->getId() : $entity->toData();
            if (isset($id) && array_search($id, $ids)) continue;
            
            $ids[] = $id;
            $entities[$key] = $entity;
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
     * Get the id of an entity
     *
     * @param Entity $entity
     * @return mixed
     */
    protected function getEntityId(Entity $entity)
    {
        $this->assertEntity($id);
        
        if (!$id instanceof Entity\Identifiable) {
           throw new \LogicException("Unable to get entity from set by id: Entity is not Identifiable");
        }
    
        $id = $id->getId();
    }
    
    
    /**
     * Check if the entity exists in this set
     * 
     * @param mixed|Entity $id
     * @return boolean
     */
    public function contains($id)
    {
        return !is_null($this->get($id));
    }
    
    /**
     * Get an entity from the set by id
     * 
     * @param mixed|Entity $id   Entity id or Entity
     * @return Entity|null
     */
    public function get($id)
    {
        if (!is_a($this->entityClass, Entity\Identifiable::class, true)) {
            throw new \LogicException("Unable to get entity from set by id: {$this->entityClass} is not Identifiable");
        }
        
        if ($id instanceof Entity) {
            $id = $this->getEntityId($entity);
        }
        
        if (!isset($id)) return null;
        
        foreach ($this->entities as $entity) {
            if (!$id instanceof Entity\Identifiable) continue; // Shouldn't happen
            if ($entity->getId() === $id) return $entity;
        }
        
        return null;
    }
    
    /**
     * Add an entity to the set
     * 
     * @param Entity|array $entity  Entity or data representation of entity
     */
    public function add($entity)
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
        return new static($this->getEntityClass(), $this->entities, $this->totalCount, $flags);
    }

    /**
     * Sort the entities as string or on a property.
     * 
     * @internal This should be ported to Jasny\DB\EntitySet
     * 
     * @param string $property
     * @return $this
     */
    public function sort($property = null)
    {
        usort($this->entities, function($a, $b) use($property) {
            $valA = isset($property) ? (isset($a->$property) ? $a->$property : null) : (string)$a;
            $valB = isset($property) ? (isset($b->$property) ? $b->$property : null) : (string)$b;
            
            return strcmp($valA, $valB);
        });
        
        return $this;
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
        $this->assertIndex($index);
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
        $this->assertEntity($entity);
        
        if (~$this->flags & self::ALLOW_DUPLICATES && $this->contains($entity)) {
            if (isset($index)) $this->offsetUnset($index);
            return;
        }

        if (!isset($index)) {
            $this->entities[] = $entity;
        } else {
            $this->assertIndex($index, true);
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
        $this->assertIndex($index);
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
        // No lazy loading, so nothing to do
        if (!is_a($this->entityClass, Entity\LazyLoading::class, true)) return $this;
        
        foreach ($this->entities as $i => $entity) {
            if (!$entity instanceof Entity\LazyLoading) continue; // Shouldn't happen
        
            $entity->expand($opts);
            if ($entity->isGhost()) unset($this->entities[$i]);
        }
        
        if (~$this->flags & self::PRESERVE_KEYS) {
            $this->entities = array_values($this->entities);
        }
        
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
     * Convert loaded values to an entityset.
     * 
     * @param array $values
     * @return static
     */
    public static function fromData($values)
    {
        return new static($values);
    }
    
    /**
     * Get data that needs stored in the DB
     * 
     * @return array
     */
    public function toData()
    {
        $data = [];
        
        foreach ($this->entities as $key => $entity) {
            $data[$key] = $entity->toData();
        }
        
        return $data;
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
        
        foreach ($this->entities as $key => $entity) {
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
