<?php

namespace Jasny\DB\Entity;

/**
 * Use metadata and type casting for entities.
 * 
 * This trait implements \Jasny\Meta\Introspection and \Jasny\Meta\TypedObject
 * 
 * @author  Arnold Daniels <arnold@jasny.net>
 * @license https://raw.github.com/jasny/db-mongo/master/LICENSE MIT
 * @link    https://jasny.github.io/db-mongo
 */
trait Meta
{
    use \Jasny\Meta\TypeCasting;
    
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
     * Cast value to a non-internal type
     * 
     * @param mixed  $value
     * @param string $type
     * @return \Jasny\DB\Entity|object
     */
    protected static function castValueToClass($value, $type)
    {
        if (!class_exists($type)) throw new \Exception("Invalid type '$type'");

        if (is_a($type, '\Jasny\DB\Entity\LazyLoading', true)) return $type::lazyload($value);
        if (is_a($type, '\Jasny\DB\Entity\ActiveRecord', true)) return $type::fetch($value);

        if (class_exists($type . 'Mapper')) {
            $mapper = $type . 'Mapper';
            $mapper::fetch($value);
        }
        
        return new $type($value);
    }
}
