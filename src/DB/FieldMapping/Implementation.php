<?php

namespace Jasny\DB\FieldMapping;

/**
 * Implementation for Field Mapping.
 * 
 * @author  Arnold Daniels <arnold@jasny.net>
 * @license https://raw.github.com/jasny/db/master/LICENSE MIT
 * @link    https://jasny.github.io/db
 */
trait Implementation
{
    /**
     * Get the field map.
     * 
     * @return array
     */
    protected static function getFieldMap()
    {
        return [];
    }
    
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
            static::mapFieldsForArray($values, $map) :
            static::mapFieldsForObject($values, $map);
        
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
        list($props, $operators) = is_array($values) ?
            static::extractOperatorsForMapping($values) :
            [get_object_vars($values), null];
        
        $map = array_intersect_key(array_flip(static::getFieldMap()), $props);
        
        if (empty($map)) return $values;
        
        $mapped = is_array($props) ?
            static::mapFieldsForArray($props, $map) :
            static::mapFieldsForObject($props, $map);
        
        if (!empty($operators)) $mapped = static::insertOperatorsForMapping($mapped, $operators);
        
        return $mapped;
    }
    
    /**
     * Extract operators from property name.
     * 
     * @param array $values
     * @return array
     */
    protected static function extractOperatorsForMapping(array $values)
    {
        $props = [];
        $operators = [];
        
        foreach ($values as $key => $value) {
            if (strpos($key, ' ') === false) {
                $props[$key] = $value;
            } else {
                list($field, $op) = explode(' ', $key, 2);
                $props[$field] = $value;
                $operators[$field] = $op;
            }
        }
        
        return [$props, $operators];
    }
    
    /**
     * Insert operators to field name.
     * 
     * @param array $values
     * @param array $operators
     * @return array
     */
    protected static function insertOperatorsForMapping(array $values, array $operators)
    {
        $fields = [];
        
        foreach ($values as $key => $value) {
            if (isset($operators[$key])) $key .= ' ' . $operators[$key];
            $fields[$key] = $value;
        }
        
        return $fields;
    }
    
    /**
     * Change array keys based on the map
     * 
     * @param array $values
     * @param array $map
     * @return array
     */
    protected static function mapFieldsForArray(array $values, array $map)
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
    protected static function mapFieldsForObject($values, array $map)
    {
        foreach ($map as $from => $to) {
            $values->$to = $values->$from;
            unset($values->$from);
        }
        
        return $values;
    }
}
