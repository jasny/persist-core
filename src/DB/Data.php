<?php

namespace Jasny\DB;

/**
 * Object than has a data representation
 */
interface Data
{
    /**
     * Convert loaded values to a model object.
     * 
     * @param object $values
     * @return static
     */
    public static function fromData($values);
    
    /**
     * Get data that needs stored in the DB
     * 
     * @return array
     */
    public function toData();
}
