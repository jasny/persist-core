<?php

namespace Jasny\DB;

use Jasny\DB\Connection;
use Jasny\DB\ConfigurationException;

/**
 * Create DB connections
 */
class ConnectionFactory
{
    /**
     * List of supported drivers with class names
     * @var array
     */
    public $drivers = [
        'mysql' => 'Jasny\DB\MySQL\Connection',
        'mysqli' => 'Jasny\DB\MySQL\Connection',
        'mongo' => 'Jasny\DB\Mongo\DB',
        'mongodb' => 'Jasny\DB\Mongo\DB',
        'rest' => 'Jasny\DB\REST\Client'
    ];
    
    /**
     * Get the connection class.
     * 
     * @param string $driver
     * @return string
     */
    protected function getConnectionClass($driver = null)
    {
        $key = isset($driver) ? strtolower($driver) : $this->guessDriver();
        
        if (!isset($this->drivers[$key])) {
            throw new ConfigurationException("Unknown DB driver '$driver'");
        }
        
        $class = $this->drivers[$key];
        
        if (!is_a($class, Connection::class, true)) {
            throw new ConfigurationException("Invalid driver '$driver': $class is not a Jasny\DB\Connection");
        }
        
        return $class;
    }

    /**
     * Guess the driver
     */
    protected function guessDriver()
    {
        $supported = [];

        foreach (array_unique($this->drivers) as $driver => $class) {
            if (class_exists($class)) {
                $supported[] = $driver;
            }
        }

        if (empty($supported)) {
            throw new ConfigurationException("No Jasny DB drivers found");
        }

        if (count($supported) > 1) {
            throw new ConfigurationException(
                "Please specify the database driver. " .
                "The following are supported: " . join(', ', $supported)
            );
        }

        return reset($supported); // Exactly one driver is installed
    }
    
    /**
     * Create a new database connection
     * 
     * @param array|mixed $settings
     * @return Connection
     */
    public function create($settings)
    {
        if (is_array($settings)) {
            $settings = (object)$settings;
        }
        
        $class = $this->getConnectionClass(isset($settings->driver) ? $settings->driver : null);
        return new $class($settings);
    }
}
