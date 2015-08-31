<?php

namespace Jasny\DB;

/**
 * Entities that supports property/field mapping.
 * 
 * @author  Arnold Daniels <arnold@jasny.net>
 * @license https://raw.github.com/jasny/db/master/LICENSE MIT
 * @link    https://jasny.github.io/db
 */
interface FieldMapping
{
    /**
     * Map field names to property names.
     * 
     * @param array|object $values
     * @return array
     */
    public static function mapFromFields($values);
    
    /**
     * Map property names to field names.
     * 
     * @param array|object $values
     * @return array
     */
    public static function mapToFields($values);
}
