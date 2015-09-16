<?php

namespace Jasny\DB\Entity\Meta;

use Jasny\DB\Entity,
    Jasny\DB\EntitySet,
    Jasny\DB\DataMapper,
    Jasny\Meta\TypeCasting;

/**
 * Use metadata and type casting for entities.
 * 
 * This trait implements Jasny\Meta\Introspection and Jasny\Meta\TypedObject
 * 
 * @author  Arnold Daniels <arnold@jasny.net>
 * @license https://raw.github.com/jasny/db-mongo/master/LICENSE MIT
 * @link    https://jasny.github.io/db-mongo
 */
trait Implementation
{
    use TypeCasting {
        castValueToArray as private _typecasting_castValueToArray;
    }
    
    /**
     * Cached meta data
     * @var Meta
     */
    protected static $meta__;
    
    /**
     * Get metadata
     * 
     * @return \Jasny\Meta
     */
    public static function meta()
    {
        $class = get_called_class();
        if (!isset(self::$meta__[$class])) {
            self::$meta__[$class] = \Jasny\Meta::fromAnnotations(new \ReflectionClass($class));
        }
        
        return self::$meta__[$class];
    }
    
    /**
     * Get the identity property/properties
     * 
     * @return string|key
     */
    public static function getIdProperty()
    {
        $key = [];
        
        foreach (static::meta()->ofProperties() as $prop => $meta) {
            if (isset($meta['id'])) $key[] = $prop;
        }
        
        return empty($key) ? null : (count($key) === 1 ? $key[0] : $key);
    }

    /**
     * Cast value to a typed array
     *
     * @param mixed  $value
     * @param string $subtype  Type of the array items
     * @return array|EntitySet
     */
    protected static function castValueToArray($value, $subtype = null)
    {
        $array = self::_typecasting_castValueToArray($value, $subtype);
        
        if (isset($subtype) && is_a($subtype, Entity::class, true)) {
            $array = $subtype::entitySet($array);
        }
        
        return $array;
    }
    
    /**
     * Cast value to a non-internal type
     * 
     * @param mixed  $value
     * @param string $type
     * @return \Jasny\DB\Entity|object
     */
    protected static function castValueToClass($value, $type)
    {
        if (!class_exists($type)) throw new \Exception("Invalid type '$type'");

        if (is_object($value) && is_a($value, $type)) return $value;
        
        if (is_a($type, Entity::class, true)) {
            if (is_a($type, Entity\LazyLoading::class, true)) return $type::lazyload($value);
            if (is_a($type, Entity\ActiveRecord::class, true)) return $type::fetch($value);

            $mapper = $type . 'Mapper';
            if (class_exists($mapper) && is_a($mapper, DataMapper::class, true)) {
                return $mapper::fetch($value);
            }
            
            return $type::fromData($value);
        }
                
        return new $type($value);
    }
}
