<?php

namespace Jasny\DB\Entity\Meta;

use stdClass;
use Jasny\Meta;
use Jasny\DB;

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
    use Meta\TypeCastingImplementation;
    
    /**
     * Cached meta data per class
     * @var Meta[]
     */
    private static $i__meta;
    
    /**
     * Get cached meta for class
     * 
     * @param string $class
     * @return Meta
     */
    final protected static function getCachedMeta($class)
    {
        return isset(self::$i__meta[$class]) ? self::$i__meta[$class] : null;
    }
    
    /**
     * Set cached meta for class
     * 
     * @param string $class
     * @param Meta   $meta
     */
    final protected static function cacheMeta($class, Meta $meta)
    {
        self::$i__meta[$class] = $meta;
    }
    
    /**
     * Get metadata
     * 
     * @return Meta
     */
    public static function meta()
    {
        $class = get_called_class();
        $meta = static::getCachedMeta($class);
        
        if (!isset($meta)) {
            $meta = Meta::fromAnnotations(new \ReflectionClass($class));
            static::cacheMeta($class, $meta);
        }
        
        return $meta;
    }
    
    
    /**
     * Get the identity property/properties
     * 
     * @return string|array
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
     * Get type cast object
     * 
     * @return DB\TypeCast
     */
    protected function typeCast($value)
    {
        $typecast = DB\TypeCast::value($value);
        
        $typecast->alias('self', get_class($this));
        $typecast->alias('static', get_class($this));
        
        return $typecast;
    }
    

    /**
     * Filter object for json serialization
     * 
     * @param stdClass $object
     * @return stdClass
     */
    protected function jsonSerializeFilter(stdClass $object)
    {
        foreach (static::meta()->ofProperties() as $prop => $meta) {
            if ($meta['censored']) {
                unset($object->$prop);
            }
        }
        
        return $object;
    }
}
