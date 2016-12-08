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
     * Flags for creating an entityset
     * @var int
     */
    protected $entitySetFlags;
    
    /**
     * Set the flags that should be used when creating an EntitySet
     * 
     * @param int $flags
     * @return $this
     */
    public function withEntitySetFlags($flags)
    {
        $this->entitySetFlags = $flags;
        return $this;
    }
    
    /**
     * Check if one type is an Entity array and the other is an EntitySet
     * 
     * @param string $type1
     * @param string $type2
     * @return array  [$entityClass, $entitySetClass]
     */
    protected function getEntityClassAndEntitySetClass($type1, $type2)
    {
        $entityClass = null;
        $entitySetClass = null;
        
        if (substr($type1, -2) === '[]') {
            $entityClass = substr($type1, 0, -2);
            $entitySetClass = $type2;
        } elseif (substr($type2, -2) === '[]') {
            $entityClass = substr($type2, 0, -2);
            $entitySetClass = $type1;
        }
        
        if (isset($entityClass) && !is_a($entityClass, Entity::class, true)) {
            $entityClass = null;
        }
        if (isset($entitySetClass) && !is_a($entitySetClass, EntitySet::class, true)) {
            $entitySetClass = null;
        }

        return [$entityClass, $entitySetClass];
    }
    
    /**
     * Check if value is one of the types, otherwise trigger a warning.
     * For Entity arrays, use the hinted EntitySet.
     * 
     * @param array $types
     * @return mixed
     */
    public function toMultiple(array $types)
    {
        $types = $this->uniqueTypes($types);
        
        if (count($types) === 2) {
            list($entityClass, $entitySetClass) = $this->getEntityClassAndEntitySetClass($types[0], $types[1]);
            
            if (isset($entityClass) && isset($entitySetClass)) {
                return $this->toEntitySet($entityClass, $entitySetClass);
            }
        }
        
        return parent::toMultiple($types);
    }
        
    /**
     * Cast value to a typed array
     *
     * @param string $subtype  Type of the array items
     * @return array|EntitySet
     */
    public function toArray($subtype = null)
    {
        if ($this->value instanceof EntitySet) {
            $array = $this->value->getArrayCopy();
            return $this->forValue($array)->toArray($subtype);
        }
        
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

        $lazyloadFlag = is_a($entityClass, Entity\LazyLoading::class, true) ? EntitySet::LAZYLOAD : 0;
        return $entitySetClass::forClass($entityClass, $array, null, $this->entitySetFlags | $lazyloadFlag);
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
    
    
    /**
     * Flatten the value and cast
     * 
     * @param string $hint  Type hint
     * @return mixed
     */
    public function flatten($hint = null)
    {
        if (!is_array($this->value) && !$this->value instanceof \Traversable) {
            $valueType = (is_object($this->value) ? get_class($this->value) . ' ' : '') . gettype($this->value);
            throw new \BadMethodCallException("Unable to flatten: value is a $valueType and not an array");
        }
        
        list($values, $types) = $this->flattenValue();
        
        if (isset($hint)) {
            $types = array_diff(explode($hint, '|'), ['null']);
            
            foreach ($types as &$type) {
                if ($type !== 'array' && !is_a($type, EntitySet::class, true)) {
                    $type .= '[]';
                }
            }
        } else {
            $types = $this->uniqueTypes($types);
        }
        
        return $this->forValue($values)->toMultiple($types);
    }
    
    /**
     * Flatten the value
     * 
     * @return array  [values, types]
     */
    protected function flattenValue()
    {
        $values = [];
        $types = [];
        
        foreach ($this->value as $value) {
            if ($value === null || $value === []) continue;
            
            $type = is_object($value) ? get_class($value) : gettype($value);
            $types[] = $type . (is_array($value) || $value instanceof EntitySet ? '' : '[]');
            
            if ($value instanceof EntitySet) {
                $types[] = $value->getEntityClass() . '[]';
                $value = $value->getArrayCopy();
            }
            
            if (is_array($value)) {
                $values = array_merge($values, array_values($value));
            } else {
                $values[] = $value;
            }
        }
        
        return [$values, $types];
    }
    
    /**
     * Remove duplicate and child types
     * 
     * @param array $typeList
     * @return array
     */
    protected function uniqueTypes($typeList)
    {
        $types = array_diff(array_unique($typeList), ['null']);
        $isArray = true;
        
        foreach ($types as $i => $type) {
            $isArray &= $type === 'array' || is_a($type, EntitySet::class, true) || strstr($type, '[]');
            
            foreach ($types as $parentType) {
                if ($type === $parentType) continue;
                if (strstr($type, '[]') !== strstr($parentType, '[]')) continue;
                
                $class = str_replace('[]', '', $type);
                $parentClass = str_replace('[]', '', $parentType);
                
                if (is_a($class, $parentClass, true)) {
                    unset($types[$i]);
                    continue 2;
                }
            }
        }
        
        if ($isArray && count($types) > 1) {
            $types = array_diff($types, ['array']);
        }
        
        return array_values($types);
    }
}
