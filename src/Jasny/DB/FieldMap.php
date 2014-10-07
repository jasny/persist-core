<?php

namespace Jasny\DB;

/**
 * Simple implementation for Field Mapping.
 * 
 * @author  Arnold Daniels <arnold@jasny.net>
 * @license https://raw.github.com/jasny/db/master/LICENSE MIT
 * @link    https://jasny.github.io/db
 */
trait FieldMap
{
    /**
     * Get the field map.
     * 
     * @return array
     */
    abstract protected static function getFieldMap();
    
    /**
     * Map field names to property names.
     * 
     * @param array|object $values
     * @return array
     */
    public static function mapFromFields($values)
    {
        $fields = is_array($values) ? $values : get_object_vars($values);
        $map = array_intersect_key(static::getFieldMap(), $fields);
        
        if (!empty($map)) $values = is_array($values) ?
            static::mapArrayKeys($values, $map) :
            static::mapPropertyNames($values, $map);
        
        return $values;
    }
    
    /**
     * Map property names to field names.
     * 
     * @param array|object $values
     * @return array
     */
    public static function mapToFields($values)
    {
        $props = is_array($values) ? $values : get_object_vars($values);
        $map = array_intersect_key(array_flip(static::getFieldMap()), $props);
        
        if (!empty($map)) $values = is_array($values) ?
            static::mapArrayKeys($values, $map) :
            static::mapPropertyNames($values, $map);
        
        return $values;
    }
    
    /**
     * Change array keys based on the map
     * 
     * @param array $values
     * @param array $map
     * @return array
     */
    protected static function mapArrayKeys(array $values, array $map)
    {
        foreach ($map as $from => $to) {
            $values[$to] = $values[$from];
            unset($values[$from]);
        }
        
        return $values;
    }
    
    /**
     * Change object properties based on the map
     * 
     * @param object $values
     * @param array $map
     * @return array
     */
    protected static function mapPropertyNames($values, array $map)
    {
        foreach ($map as $from => $to) {
            $values->$to = $values->$from;
            unset($values->$from);
        }
        
        return $values;
    }
}
