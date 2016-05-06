<?php

namespace Jasny\DB\Connection\Namable;

use Jasny\DB;

/**
 * Implementation for named connections using the Jasny\DB register
 * 
 * @author  Arnold Daniels <arnold@jasny.net>
 * @license https://raw.github.com/jasny/db/master/LICENSE MIT
 * @link    https://jasny.github.io/db
 */
trait Implemention
{
    /**
     * Name the connection, making it globally available.
     * 
     * @param string $name
     */
    public function useAs($name)
    {
        DB::connections()->register($name, $this);
    }
    
    /**
     * Get the name of the connection.
     * If the connection has multiple names, returns the first one.
     * 
     * @return string|null
     */
    public function getConnectionName()
    {
        return DB::connections()->getRegisteredName($this);
    }
}
