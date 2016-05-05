<?php

namespace Jasny\DB;

use Jasny\DB;
use Jasny\DB\Entity;
use Jasny\DB\EntitySet;

/**
 * Type casting for properties of DB entities
 */
class TypeCast extends \Jasny\TypeCast
{
    /**
     * Cast value to a typed array
     *
     * @param mixed  $value
     * @param string $subtype  Type of the array items
     * @return array|EntitySet
     */
    public function toArray($subtype = null)
    {
        return isset($subtype) && is_a($subtype, Entity::class, true)
            ? $this->toEntitySet($subtype)
            : parent::toArray($subtype);
    }
    
    /**
     * Cast value to an entity set
     * 
     * @param string $entityClass     Entity class
     * @param string $entitySetClass  EntitySet class
     * @return EntitySet
     */
    public function toEntitySet($entityClass, $entitySetClass = null)
    {
        if (is_a($this->value, $entitySetClass ?: EntitySet::class)) {
            if ($this->value->getEntityClass() !== $entityClass) {
                $setClass = $this->value->getEntityClass();
                trigger_error("Unable to cast set of $setClass entities to $entityClass entities", E_USER_WARNING);
            }
            
            return $this->value;
        }
        
        if (!isset($entitySetClass)) {
            $entitySetClass = DB::entitySetFactory()->getClass($entityClass);
        } elseif (!is_a($entitySetClass, EntitySet::class, true)) {
            throw new \InvalidArgumentException("$entitySetClass is not a Jasny\DB\EntitySet");
        }
        
        $array = $this->toArray();
        
        return is_a($entityClass, Entity\LazyLoading::class, true)
            ? $entitySetClass::lazyload($array)
            : $entitySetClass::fromData($array);
    }
    
    
    /**
     * Cast value to a class object
     * 
     * @param string $type
     * @return Entity|object
     */
    public function toClass($type)
    {
        if (is_a($this->value, $type)) {
            return $this->value;
        }
        
        return is_a($type, Entity::class, true)
            ? $this->toEntity($type)
            : parent::toClass($type);
    }
    
    /**
     * Cast value to Entity object
     * 
     * @param type $entityClass
     * @return type
     */
    public function toEntity($entityClass)
    {
        if (is_null($this->value)) {
            return null;
        }
        
        return is_a($entityClass, Entity\LazyLoading::class, true)
            ? $entityClass::lazyload($this->value)
            : $entityClass::fromData($this->value);
    }
}
