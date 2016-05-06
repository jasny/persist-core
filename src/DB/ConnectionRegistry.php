<?php

namespace Jasny\DB;

use Jasny\DB;
use Jasny\DB\Connection;
use Jasny\DB\ConfigurationException;

/**
 * Registry for DB connections
 */
class ConnectionRegistry
{
    /**
     * Named connections
     * @var Connection[]
     */
    protected $connections = [];
    
    
    /**
     * Get configuration settings for a connection.
     * 
     * @param string $name
     * @return array|mixed
     */
    protected function getSettings($name)
    {
        return DB::getSettings($name);
    }
    
    /**
     * Create a database connection
     * 
     * @param string $name
     * @return Connection|null
     */
    protected function createConnection($name)
    {
        $settings = $this->getSettings($name);
        
        if (!isset($settings) && $name !== 'default') {
            return null;
        }
        
        return DB::createConnection($settings);
    }
    
    
    /**
     * Get a named DB connection.
     * 
     * @param string $name
     * @return Connection
     */
    public function get($name)
    {
        if (!isset($this->connections[$name])) {
            $conn = $this->createConnection($name);
            
            if (!isset($conn)) {
                throw new ConfigurationException("DB connection named '$name' doesn't exist");            
            }
            
            $this->connections[$name] = $conn;
        }
        
        return $this->connections[$name];
    }
    
    
    /**
     * Register a DB connection
     * 
     * @param string     $name
     * @param Connection $conn
     */
    public function register($name, Connection $conn)
    {
        $this->connections[$name] = $conn;
    }
    
    /**
     * Unregister a connection
     * 
     * @param string|Connection $conn
     */
    public function unregister($conn)
    {
        $names = is_string($conn) ? [$conn] : array_keys($this->connections, $conn, true);
        
        foreach ($names as $name) {
            unset($this->connections[$name]);
        }
    }
    
    /**
     * Get the name of the connection.
     * If the connection has multiple names, returns the first one.
     * 
     * @param Connection $conn
     * @return string|null
     */
    public function getRegisteredName(Connection $conn)
    {
        $index= array_search($conn, $this->connections, true);
        return $index !== false ? $index : null;
    }
}
