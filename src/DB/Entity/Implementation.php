<?php

namespace Jasny\DB\Entity;

use stdClass;
use ReflectionClass;
use Jasny\DB;
use Jasny\DB\EntitySet;
use Jasny\DB\Entity\Dynamic;
use Jasny\DB\Data;

/**
 * Basic implementation for an entity
 * 
 * @author  Arnold Daniels <arnold@jasny.net>
 * @license https://raw.github.com/jasny/db/master/LICENSE MIT
 * @link    https://jasny.github.com/db
 */
trait Implementation
{
    /**
     * Set the values.
     * 
     * @param array|object $values
     * @return $this
     */
    public function setValues($values)
    {
        $refl = new ReflectionClass($this);

        foreach ($values as $key => $value) {
            $skip = $refl->hasProperty($key)
                ? (!$refl->getProperty($key)->isPublic() || $refl->getProperty($key)->isStatic())
                : ($key[0] === '_' || !$this instanceof Dynamic);

            if ($skip) {
                continue;
            }

            $this->$key = $value;
        }
        
        return $this;
    }

    /**
     * Get the values.
     * 
     * @return $this
     */
    public function getValues()
    {
        if ($this instanceof LazyLoading && $this->isGhost()) {
            $this->expand();
        }
        
        return DB::getPublicProperties($this);
    }
    
    
    /**
     * Convert loaded values to an entity.
     * Calls the construtor *after* setting the properties.
     * 
     * @param array|stdClass $values
     * @return static
     */
    public static function fromData($values)
    {
        if (!is_array($values) && !$values instanceof stdClass) {
            $type = (is_object($values) ? get_class($values) . ' ' : '') . gettype($values);
            throw new \InvalidArgumentException("Expected an array or stdClass object, but got a $type");
        }

        $class = get_called_class();
        $reflection = new \ReflectionClass($class);
        $entity = $reflection->newInstanceWithoutConstructor();

        // manual set properties instead of setValues, because that function might be overridden
        foreach ($values as $key => $value) {
            $skip = $reflection->hasProperty($key)
                ? (!$reflection->getProperty($key)->isPublic() || $reflection->getProperty($key)->isStatic())
                : ($key[0] === '_' || !$entity instanceof Dynamic);

            if ($skip) {
                continue;
            }

            $entity->$key = $value;
        }

        if (method_exists($entity, '__construct')) {
            $entity->__construct();
        }
        
        return $entity;
    }
    
    /**
     * Get the data that needs to be stored in the DB
     * 
     * @return array
     */
    public function toData()
    {
        $values = DB::getPublicProperties($this);
        
        foreach ($values as $key => &$item) {
            if (!$this instanceof Dynamic && !property_exists(get_class($this), $key)) {
                unset($values[$key]);
                continue;
            }
            
            if ($item instanceof Identifiable) {
                $item = $item->getId();
            } elseif ($item instanceof Data) {
                $item = $item->toData();
            }
        }
        
        return $values;
    }
    
    
    /**
     * Prepare entity for JSON encoding
     * 
     * @return object
     */
    public function jsonSerialize()
    {
        $values = $this->getValues();
        
        foreach ($values as &$value) {
            if ($value instanceof \DateTime) {
                $value = $value->format(\DateTime::ISO8601);
            }
            
            if ($value instanceof EntitySet) {
                $value->expand();
            }
        }
        
        return $this->jsonSerializeFilter((object)$values);
    }
    
    /**
     * Filter object for json serialization.
     * This method will call other methods that start with jsonSerializeFilter
     * 
     * @param stdClass $object
     * @return stdClass
     */
    protected function jsonSerializeFilter(stdClass $object)
    {
        $refl = new \ReflectionClass($this);
        foreach ($refl->getMethods() as $method) {
            if (strpos($method->getName(), __FUNCTION__) === 0 && $method->getName() !== __FUNCTION__) {
                $fn = $method->getName();
                $object = $this->$fn($object);
            }
        }
        
        return $object;
    }
    
    
    /**
     * Create an entity set
     * 
     * @deprecated since v2.4.0
     * @see DB::entitySet()
     * 
     * @param Entities[]|\Traversable $entities  Array of entities
     * @param int|\Closure            $total     Total number of entities (if set is limited)
     * @param int                     $flags     Control the behaviour of the entity set
     * @param mixed                   ...        Additional are passed to the constructor
     * @return EntitySet
     */
    public static function entitySet($entities = [], $total = null, $flags = 0)
    {
        $entityClass = get_called_class();
        $args = func_get_args();
        
        $entitySetClass = DB::entitySetFactory()->getClass($entityClass);
        return $entitySetClass::forClass($entityClass, ...$args);
    }
}
