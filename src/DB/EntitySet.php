<?php

namespace Jasny\DB;

use Jasny\DB\Entity;
use Jasny\DB\Data;
use Jasny\Meta\Introspection;
use Jasny\DB\TypeCast;

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
    public static function forClass($class, $entities = [], $total = null, $flags = 0)
    {
        $refl = new \ReflectionClass(get_called_class());
        
        $entitySet = $refl->newInstanceWithoutConstructor();
        $entitySet->entityClass = $class;
        
        $args = func_get_args();
        array_shift($args);
        
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
            throw new \DomainException("a $class is not an Entity");
        }
        
        if (
            isset($this->entityClass) &&
            isset($class) &&
            $class !== $this->entityClass &&
            !is_a($class, $this->entityClass, true)
        ) {
            $setClass = get_class($this);
            throw new \DomainException("a $setClass is only for {$this->entityClass} entities, not $class");
        }
        
        if (!empty($this->entities) && $class !== $this->entityClass) {
            throw new \LogicException("Can't change the entity class to '$class': set already contains entities");
        }
        
        $this->entityClass = $class;
    }
    
    /**
     * Turn input into array of entities
     * 
     * @param Entity|mixed $entity
     */
    protected function assertEntity($entity)
    {
        if (!$entity instanceof Entity) {
            $type = (is_object($entity) ? get_class($entity) . ' ' : '') . gettype($entity);
            throw new \InvalidArgumentException("A $type is not an Entity");
        }
        
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
     * @param array|\Traversable $input
     * @return array
     */
    protected function castEntities($input)
    {
        if (!is_array($input) && !$input instanceof \Traversable) {
            $type = (is_object($input) ? get_class($input).' ' : '') . gettype($input);
            throw new \InvalidArgumentException("Input should either be an array or Traverable, not a $type");
        }
        
        $items = [];
        $entities = [];
        
        if (~$this->flags & self::PRESERVE_KEYS) {
            foreach ($input as $item) {
                $items[] = $item;
            }
        } else {
            $items = $input;
        }
        
        foreach ($items as $key => $item) {
            $entity = $this->castEntity($item);
            $this->assertEntity($entity);
            
            $entities[$key] = $entity;
        }
        
        return $entities;
    }
    
    /**
     * Turn item into entity
     * 
     * @param mixed $item
     * @return Entity
     */
    protected function castEntity($item)
    {
        if ($item instanceof Entity) {
            return $item;
        }
        
        if (!isset($this->entityClass)) {
            throw new \InvalidArgumentException("Unable to cast: entity class not set");
        }

        $class = $this->entityClass;
        
        if ($this->flags & self::LAZYLOAD && is_a($class, Entity\LazyLoading::class, true)) {
            return $class::lazyload($item);
        }
        
        if (!is_a($class, Data::class, true)) {
            throw new \LogicException("Unable to cast: $class doesn't implement the " . Data::class . " interface");
        }
        
        return $class::fromData($item);
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
            $id = $this->getEntityId($entity);
            if (isset($id) && in_array($id, $ids)) continue;
            
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
     * @return Entity[]
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
        $this->assertEntity($entity);
        
        if (!$entity instanceof Entity\Identifiable) {
           $class = get_class($entity);
           throw new \LogicException("Unable to get entity id: {$class} is not Identifiable");
        }
    
        return $entity->getId();
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
        if ($id instanceof Entity) {
            $id = $this->getEntityId($id);
        }
        
        if (!isset($id)) return null;
        
        foreach ($this->entities as $entity) {
            if ($this->getEntityId($entity) === $id) return $entity;
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
        
        $uniqueSet = clone $this;
        $uniqueSet->flags = $uniqueSet->flags & ~static::ALLOW_DUPLICATES;
        $uniqueSet->entities = $uniqueSet->uniqueEntities($uniqueSet->entities);
        
        return $uniqueSet;
    }
    
    /**
     * Filter the elements
     * 
     * @param array $filter
     * @return static
     */
    public function filter(array $filter)
    {
        $filteredSet = clone $this;
        $filteredSet->flags = $filteredSet->flags & ~static::ALLOW_DUPLICATES;
        
        $filteredSet->entities = array_filter($filteredSet->entities, function($entity) use ($filter) {
            $valid = true;
            
            foreach ($filter as $key => $value) {
                $valid = $valid && !isset($entity->$key)
                    ? !isset($value)
                    : ($value == $entity->$key || (is_array($entity->$key) && in_array($value, $entity->$key)));
            }
            
            return $valid;
        });
        
        return $filteredSet;
    }

    /**
     * Sort the entities as string or on a property.
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
     * @param int          $index
     * @param Entity|array $entity  Entity or data representation of entity
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
    final public static function fromData($values)
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
        $flatten = true;
        
        foreach ($this->entities as $key => $entity) {
            $result = call_user_func_array([$entity, $name], $arguments);
            $results[$key] = $result;
            $flatten = $flatten && ($result instanceof self || $result instanceof Entity);
        }
        
        if ($results === $this->entities) {
            return $this;
        }
        
        if ($flatten && !empty($results)) {
            $results = TypeCast::value($results)->withEntitySetFlags(self::ALLOW_DUPLICATES)->flatten();
        }
        
        return $results;
    }
    
    /**
     * Get property type (through var meta data)
     * 
     * @param string $property
     * @return string
     */
    protected function getEntityPropertyType($property)
    {
        $entityClass = $this->getEntityClass();

        return is_a($entityClass, Introspection::class, true)
            ? $entityClass::meta()->ofProperty($property)->get('var')
            : null;
    }
    
    /**
     * Get property of entities as array.
     * If property is an EntitySet, it will be flattened.
     * 
     * @param string $property
     * @return EntitySet|array
     */
    public function __get($property)
    {
        $results = [];
        $flatten = true;
        
        foreach ($this->entities as $key => $entity) {
            if (!isset($entity->$property)) continue;
            $results[$key] = $entity->$property;
            $flatten = $flatten &&
                ($entity->$property instanceof self || $entity->$property instanceof Entity);
        }

        if ($flatten && !empty($results)) {
            $hint = $this->getEntityPropertyType($property);
            $results = TypeCast::value($results)->withEntitySetFlags(self::ALLOW_DUPLICATES)->flatten($hint);
        }
        
        return $results;
    }
}
