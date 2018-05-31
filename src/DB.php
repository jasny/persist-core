<?php

namespace Jasny;

use Jasny\DB\Connection;
use Jasny\DB\ConnectionFactory;
use Jasny\DB\ConnectionRegistry;
use Jasny\DB\EntitySetFactory;
use Jasny\DB\Entity\Dynamic;

/**
 * DB factories and registries.
 * 
 * @interal Methods that are used by the library are made `final`. Overwriting them in would give unexpected results.
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
     * @deprecated since v2.4.0
     * @see DB::configure()
     * @access private
     * 
     * @var object
     */
    public static $config;
    
    /**
     * @var ConnectionFactory
     */
    private static $connectionFactory;
    
    /**
     * @var ConnectionRegistry
     */
    private static $connectionRegistry;
    
    /**
     * @var EntitySetFactory
     */
    private static $entitySetFactory;

    
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
     * @return array|object|null
     */
    final public static function getSettings($name)
    {
        return isset(self::$config->$name) ? self::$config->$name : null;
    }

    /**
     * Use a custom factory
     * 
     * @param ConnectionFactory|ConnectionRegistry|EntitySetFactory $custom
     */
    public static function employ($custom)
    {
        if ($custom instanceof ConnectionFactory) {
            self::$connectionFactory = $custom;
        } elseif ($custom instanceof ConnectionRegistry) {
            self::$connectionRegistry = $custom;
        } elseif ($custom instanceof EntitySetFactory) {
            self::$entitySetFactory = $custom;
        } else {
            $expected = "Jasny\DB\ConnectionFactory, Jasny\DB\ConnectionRegistry or Jasny\DB\EntitySetFactory";
            $type = (is_object($custom) ? get_class($custom) . ' ' : '') . gettype($custom);
            
            throw new \InvalidArgumentException("Expected a $expected object, but got a $type");
        }
    }
    

    /**
     * Get the connection factory
     * 
     * @return ConnectionFactory
     */
    final public static function connectionFactory()
    {
        if (!isset(self::$connectionFactory)) {
            self::$connectionFactory = new ConnectionFactory();
        }
        
        return self::$connectionFactory;
    }
    
    /**
     * Create a new database connection
     * 
     * @param array|mixed $settings  Configuration settings
     * @return Connection
     */
    final public static function createConnection($settings)
    {
        return self::connectionFactory()->create($settings);
    }
    
    
    /**
     * Get connection registry
     * 
     * @return ConnectionRegistry
     */
    final public static function connections()
    {
        if (!isset(self::$connectionRegistry)) {
            self::$connectionRegistry = new ConnectionRegistry();
        }
        
        return self::$connectionRegistry;
    }
    
    /**
     * Get a named DB connection.
     * 
     * @param string $name  Name
     * @return static
     */
    final public static function conn($name = 'default')
    {
        return self::connections()->get($name);
    }
    
    /**
     * Register a DB connection
     * 
     * @deprecated since v2.4.0
     * @see Jasny\DB\ConnectionRegistry::register()
     * 
     * @param string     $name  Name
     * @param Connection $conn  Connection
     */
    final public static function register($name, Connection $conn)
    {
        return self::connections()->register($name, $conn);
    }
    
    /**
     * Unregister a connection
     * 
     * @deprecated since v2.4.0
     * @see Jasny\DB\ConnectionRegistry::unregister()
     * 
     * @param string|Connection $conn  Name or connection
     */
    final public static function unregister($conn)
    {
        return self::connections()->unregister($conn);
    }
    
    /**
     * Get the name of the connection.
     * If the connection has multiple names, returns the first one.
     * 
     * @deprecated since v2.4.0
     * @see Jasny\DB\ConnectionRegistry::getRegisteredName()
     * 
     * @param Connection $conn
     * @return string|null
     */
    final public static function getRegisteredName(Connection $conn)
    {
        return self::connections()->getRegisteredName($conn);
    }

    
    /**
     * Get the entitySet factory
     * 
     * @return EntitySetFactory
     */
    final public static function entitySetFactory()
    {
        if (!isset(self::$entitySetFactory)) {
            self::$entitySetFactory = new EntitySetFactory();
        }
        
        return self::$entitySetFactory;
    }
    
    /**
     * Create an entity set
     * 
     * @param string                  $entityClass  Entity or entity class
     * @param Entities[]|\Traversable $entities     Array of entities
     * @param int|\Closure            $total        Total number of entities (if set is limited)
     * @param int                     $flags        Control the behaviour of the entity set
     * @param mixed                   ...           Additional arguments are passed to the constructor
     * @return EntitySet
     */
    final public static function entitySet($entityClass, $entities = [], $total = null, $flags = 0)
    {
        $args = func_get_args();
        
        if (is_callable([$entityClass, 'entitySet'])) {
            array_shift($args);
            return $entityClass::entitySet(...$args); // BC v2.3
        }
        
        return self::entitySetFactory()->create(...$args);
    }
    
    
    /**
     * Reset the global state.
     * Should be run before each unit test.
     */
    public static function resetGlobalState()
    {
        self::$config = null;
        
        self::$connectionFactory = null;
        self::$connectionRegistry = null;
        self::$entitySetFactory = null;
    }


    /**
     * Set public properties of an object.
     * Utility function
     *
     * @param object $object
     * @param array  $values
     * @return object $object
     */
    public static function setPublicProperties($object, array $values)
    {
        foreach ($values as $key => $value) {
            if (!property_exists($object, $key) && ($key[0] === '_' || !$object instanceof Dynamic)) {
                continue;
            }

            $object->$key = $value;
        }
        
        return $object;
    }

    /**
     * Set public properties of an object.
     * Utility function
     *
     * @param object $object
     * @param array  $values
     * @return object $object
     */
    public static function getPublicProperties($object)
    {
        return get_object_vars($object);
    }
}

