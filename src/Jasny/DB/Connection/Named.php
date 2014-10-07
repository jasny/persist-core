<?php

namespace Jasny\DB\Connection;

use Jasny\DB;

/**
 * Implementation for named connections
 * 
 * @author  Arnold Daniels <arnold@jasny.net>
 * @license https://raw.github.com/jasny/db/master/LICENSE MIT
 * @link    https://jasny.github.io/db
 */
trait Named
{
    /**
     * Name the connection, making it globally available.
     * 
     * @param string $name
     */
    public function useAs($name)
    {
        DB::register($name, $this);
    }
    
    /**
     * Get the name of the connection.
     * If the connection has multiple names, returns the first one.
     * 
     * @return string|null
     */
    public function getConnectionName()
    {
        return DB::getRegistredName($this);
    }
}
