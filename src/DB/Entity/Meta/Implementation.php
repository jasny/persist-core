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
    use Meta\TypeCasting\Implementation;
    use Meta\Introspection\AnnotationsImplementation;
    
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
     * Filter object for json serialization.
     * @deprecated
     * 
     * @param stdClass $object
     * @return stdClass
     */
    protected function jsonSerializeFilter(stdClass $object)
    {
        return $this->jsonSerializeFilterByMeta($object);
    }

    /**
     * Filter object for json serialization
     * 
     * @param stdClass $object
     * @return stdClass
     */
    protected function jsonSerializeFilterByMeta(stdClass $object)
    {
        foreach (static::meta()->ofProperties() as $prop => $meta) {
            if ($meta['censored']) {
                unset($object->$prop);
            }
        }
        
        return $object;
    }
}
