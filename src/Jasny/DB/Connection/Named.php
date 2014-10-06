<?php

namespace Jasny\DB\Connection;

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
     * Named connections
     * @var static[]
     */
    protected static $connections = [];
    
    
    /**
     * Get a named DB connection.
     * 
     * @param string $name
     * @return static
     */
    public static function conn($name='default')
    {
        if (!isset(self::$connections[$name])) throw new \Exception("DB connection named '$name' doesn't exist");
        return self::$connections[$name];
    }

    /**
     * Name the connection, making it globally available.
     * 
     * @param string $name
     */
    public function useAs($name)
    {
        self::$connections[$name] = $this;
    }
    
    /**
     * Get the name of the connection.
     * If the connection has multiple names, returns the first one.
     * 
     * @return string|null
     */
    public function getConnectionName()
    {
        foreach (self::$connections as $name => $connection) {
            if ($connection === $this) return $name;
        }
        
        return null;
    }
}
