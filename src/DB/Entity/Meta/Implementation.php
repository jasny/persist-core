<?php

namespace Jasny\DB\Entity\Meta;

use Jasny\DB\Entity;
use Jasny\DB\EntitySet;
use Jasny\DB\DataMapper;
use Jasny\Meta;

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
     * @param Meta $meta
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
            $meta = \Jasny\Meta::fromAnnotations(new \ReflectionClass($class));
            static::setCachedMeta($class, $meta);
        }
        
        return $meta;
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
     * Create an entity set
     * 
     * @param Entities[]|\Traversable $entities  Array of entities
     * @param int|\Closure            $total     Total number of entities (if set is limited)
     * @param int                     $flags     Control the behaviour of the entity set
     * @return EntitySet
     */
    public static function entitySet($entities = [], $total = null, $flags = 0)
    {
        $setClass = static::meta()['entitySet'] ?: EntitySet::class;
        return new $setClass(get_called_class(), $entities, $total, $flags);
    }
    
    
    /**
     * Filter object for json serialization
     * 
     * @param \stdClass $object
     * @return \stdClass
     */
    protected function jsonSerializeFilter(\stdClass $object)
    {
        foreach ($object as $prop => $value) {
            if (static::meta()->of($prop)['censored']) {
                unset($object->$prop);
            }
        }
        
        return $object;
    }
}
