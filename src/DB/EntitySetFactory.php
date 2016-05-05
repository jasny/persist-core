<?php

namespace Jasny\DB;

use Jasny\DB\EntitySet;
use Jasny\Meta\Introspection;

/**
 * Factory for EntitySet
 */
class EntitySetFactory
{
   /**
     * EntitySet classes
     * @var string[]
     */
    protected $classes = [];
    
    
    /**
     * Create an entity set
     * 
     * @param string                  $entityClass  Entity or entity class
     * @param Entities[]|\Traversable $entities     Array of entities
     * @param int|\Closure            $total        Total number of entities (if set is limited)
     * @param int                     $flags        Control the behaviour of the entity set
     * @param mixed                   ...           Additional arguments are passed to the constructor
     * @return EntitySet
     */
    public function create($entityClass, $entities = [], $total = null, $flags = 0)
    {
        $class = static::getEntitySetClass($entityClass);
        $args = func_get_args();
        
        if ($class instanceof \Closure) {
            array_shift($args);
            return $class(...$args);
        }
        
        return $class::forClass(...$args);
    }

    /**
     * Get the entity set class for an entity class
     * 
     * @param string $entityClass  Entity class, omit for default
     * @return string|Closure
     */
    public function getClass($entityClass = null)
    {
        if (isset($this->classes[$entityClass])) {
            return $this->classes[$entityClass];
        }
        
        if (is_a($entityClass, Introspection::class, true) && $entityClass::meta()->get('entitySet')) {
            return $entityClass::meta()->get('entitySet');
        }
        
        if ($entityClass && class_exists($entityClass . 'Set') && is_a($entityClass . 'Set', EntitySet::class, true)) {
            return $entityClass . 'Set';
        }
        
        return EntitySet::class;
    }
    
    /**
     * Register an entity set
     * 
     * @param string         $entityClass
     * @param string|Closure $entitySetClass
     */
    public function setClass($entityClass, $entitySetClass)
    {
        if (!is_string($entitySetClass) && !$entitySetClass instanceof \Closure) {
            $msg = "EntitySet class " . ($entityClass ? "for '$entityClass' " : '') .
                "should be a class name (string) or a Closure";
            throw new \InvalidArgumentException($msg);
        }
        
        $this->classes[$entityClass] = $entitySetClass;
    }
    
    /**
     * Set the default EntitySet class
     * 
     * @param string $entitySetClass
     */
    final public function setDefaultClass($entitySetClass)
    {
        $this->setClass(null, $entitySetClass);
    }
}
