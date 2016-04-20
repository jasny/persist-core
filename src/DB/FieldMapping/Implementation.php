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
     * @param array $values
     * @return array
     */
    public static function mapFromFields(array $values)
    {
        $map = array_intersect_key(static::getFieldMap(), $values);
        
        foreach ($map as $from => $to) {
            $values[$to] = $values[$from];
            unset($values[$from]);
        }
        
        return $values;
    }
    
    /**
     * Map property names to field names.
     * 
     * @param array $values
     * @return array
     */
    public static function mapToFields(array $values)
    {
        $map = array_flip(static::getFieldMap());

        foreach ($values as $key => $value) {
            list($prop, $operator) = static::extractOperatorForMapping($key);
            
            if (isset($map[$prop])) {
                $values[$map[$prop].$operator] = $value;
                unset($values[$key]);
            }
        }
        
        return $values;
    }
    
    /**
     * Extract operators from property name.
     * 
     * @param string $key
     * @return array [property, operator]
     */
    protected static function extractOperatorForMapping($key)
    {
        $pos = strpos($key, '(');
        
        if ($pos === false) return [$key, null];
            
        $prop = rtrim(substr($key, 0, $pos));
        $operator = substr($key, $pos);
        return [$prop, $operator];
    }
}
