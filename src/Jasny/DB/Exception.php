<?php
/**
 * Jasny DB - A DB layer for the masses.
 * 
 * PHP version 5.3+
 * 
 * @author  Arnold Daniels <arnold@jasny.net>
 * @license https://raw.github.com/jasny/db/master/LICENSE MIT
 * @link    https://jasny.github.io/db
 */
/** */
namespace Jasny\DB;

/**
 * Interface for DB exceptions
 */
interface Exception
{
    /**
     * Get DB error message
     * 
     * @return string
     */
    public function getError();
    
    /**
     * Get failed query
     * 
     * @return string 
     */
    public function getQuery();
}
