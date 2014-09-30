<?php
namespace Jasny\DB;

use Jasny\Meta;

/**
 * Model entity base class
 * 
 * @author  Arnold Daniels <arnold@jasny.net>
 * @license https://raw.github.com/jasny/db/master/LICENSE MIT
 * @link    https://jasny.github.com/db
 */
abstract class Entity implements Meta\Introspection
{
    use TypeCasting;
    
    /**
     * Cached meta data
     * @var Meta
     */
    protected static $meta__;


    /**
     * Class constructor
     */
    public function __construct()
    {
        $this->cast();
    }
    
    /**
     * Get metadata
     * 
     * @return \Jasny\Meta
     */
    public static function meta()
    {
        $class = get_called_class();
        if (!isset(self::$meta__[$class])) {
            self::$meta__[$class] = Meta::fromAnnotations(new \ReflectionClass($class));
        }
        
        return self::$meta__[$class];
    }
    
    /**
     * Set the values.
     * {@interal Using Entity::setValues() should be any different than setting the properties one by one }}
     * 
     * @param array|object $values
     * @return $this
     */
    final public function setValues($values)
    {
        foreach ($values as $key=>$value) {
            $this->$key = $value;
        }
        
        return $this;
    }
    
    
    /**
     * Convert values to an entity.
     * Calls the construtor after setting the properties.
     * 
     * @param object $values
     * @return static
     */
    public static function __set_state($values)
    {
        $class = get_called_class();
        $reflection = new \ReflectionClass($class);
        $entity = $reflection->newInstanceWithoutConstructor();
        
        foreach ($values as $key=>$value) {
            $entity->$key = $value;
        }
        
        $entity->__construct();
        
        return $entity;
    }
    
    
    /**
     * Cast object to JSON
     * 
     * @return object
     */
    public function jsonSerialize()
    {
        $values = [];
        
        foreach ($this as $key=>$value) {
            if ($key[0] === "\0") continue; // Private or protected property
            
            if ($value instanceof \DateTime) $value = $value->format(\DateTime::ISO8601);
            
            $values[$key] = $value;
        }
        
        return (object)$values;
    }
}
