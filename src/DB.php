<?php

namespace Jasny;

/**
 * Connection factory and registry.
 * 
 * @author  Arnold Daniels <arnold@jasny.net>
 * @license https://raw.github.com/jasny/db/master/LICENSE MIT
 * @link    https://jasny.github.com/db
 */
class DB
{
    /**
     * Configuration (per connection)
     * 
     * @var object
     */
    public static $config;
    
    /**
     * List of supported drivers with class names
     * @var array
     */
    public static $drivers = [
        'mysql' => 'Jasny\DB\MySQL\Connection',
        'mysqli' => 'Jasny\DB\MySQL\Connection',
        'mongo' => 'Jasny\DB\Mongo\DB',
        'mongodb' => 'Jasny\DB\Mongo\DB',
        'rest' => 'Jasny\DB\REST\Client'
    ];
    
    /**
     * Named connections
     * @var \Jasny\DB\Connection[]
     */
    protected static $connections = [];
    
    
    /**
     * Should not be instantiated.
     * @ignore
     */
    private function __construct()
    {}
    
    
    /**
     * Set the configuration
     * 
     * @param array|object $config
     */
    public static function configure($config)
    {
        if (is_array($config)) {
            $config = (object)$config;
        }
        
        self::$config = $config;
    }
    
    /**
     * Get configuration settings for a connection.
     * 
     * @param string $name
     * @return array|object
     */
    public static function getSettings($name)
    {
        $config = (object)static::$config;
        return isset($config->$name) ? $config->$name : null;
    }


    /**
     * Get the connection class.
     * 
     * @param string $driver
     * @return string
     */
    protected static function getConnectionClass($driver = null)
    {
        if (!isset($driver)) {
            $supported = [];
            
            foreach (array_unique(static::$drivers) as $driver => $class) {
                if (class_exists($class)) {
                    $supported[] = $driver;
                }
            }
            
            if (empty($supported)) {
                throw new \Exception("No Jasny DB drivers found");
            }
            
            if (count($supported) > 1) {
                throw new \Exception(
                    "Please specify the database driver. " .
                    "The following are supported: " . join(', ', $supported)
                );
            }
            
            $driver = reset($supported); // Exactly one driver is installed
        } else {
            $driver = strtolower($driver);
        }
        
        if (!isset(static::$drivers[$driver])) {
            throw new \Exception("Unknown DB driver '{$driver}'");
        }
        
        return static::$drivers[$driver];
    }
    
    /**
     * Create a new database connection
     * 
     * @param array|object $settings
     * @return DB\Connection
     */
    public static function createConnection($settings)
    {
        if (is_array($settings)) {
            $settings = (object)$settings;
        }
        
        $class = static::getConnectionClass(isset($settings->driver) ? $settings->driver : null);
        return new $class($settings);
    }
    
    
    /**
     * Get a named DB connection.
     * 
     * @param string $name
     * @return static
     */
    public static function conn($name = 'default')
    {
        if (!isset(self::$connections[$name])) {
            $settings = static::getSettings($name);
            if (!$settings) {
                throw new \Exception("DB connection named '$name' doesn't exist");
            }
            
            self::$connections[$name] = static::createConnection($settings);
        }
        
        return self::$connections[$name];
    }
    
    /**
     * Register a DB connection
     * 
     * @param string        $name
     * @param DB\Connection $conn
     */
    public static function register($name, DB\Connection $conn)
    {
        self::$connections[$name] = $conn;
    }
    
    /**
     * Unregister a connection
     * 
     * @param string|DB\Connection $conn
     */
    public static function unregister($conn)
    {
        $name = is_string($conn) ? $conn : static::getRegisteredName($conn);
        
        if (isset($name)) {
            unset(self::$connections[$name]);
        }
    }
    
    /**
     * Get the name of the connection.
     * If the connection has multiple names, returns the first one.
     * 
     * @param DB\Connection $conn
     * @return string|null
     */
    public static function getRegisteredName(DB\Connection $conn)
    {
        foreach (self::$connections as $name => $cur) {
            if ($cur === $conn) {
                return $name;
            }
        }
        
        return null;
    }
}
