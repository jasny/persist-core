<?php

namespace Jasny\DB\Connection;

/**
 * Interface for named connections
 * 
 * @author  Arnold Daniels <arnold@jasny.net>
 * @license https://raw.github.com/jasny/db/master/LICENSE MIT
 * @link    https://jasny.github.io/db
 */
interface Namable extends \Jasny\DB\Connection
{
    /**
     * Name the connection, making it globally available.
     * 
     * @param string $name
     */
    public function useAs($name);
    
    /**
     * Get the name of the connection.
     * If the connection has multiple names, returns the first one.
     * 
     * @return string
     */
    public function getConnectionName();
}
