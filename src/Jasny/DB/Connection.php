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
 * Interface for any DB connection.
 */
interface Connection
{
    /**
     * Get the default DB connection.
     * 
     * @return DB
     */
    public static function conn();

    /**
     * Closes a previously opened database connection.
     */
    public function close();
    
    /**
     * Use this connection as the default DB
     */
    public function asDefault();
    
    
    /**
     * Get a table gateway.
     * 
     * @param string $name  Table name
     * @return Table
     */
    public function table($name);
    
    /**
     * Set the model namespace.
     * 
     * @param string $ns
     */
    public function setModelNamespace($ns);

    /**
     * Get the model namespace.
     * 
     * @return string
     */
    public function getModelNamespace();
    
    
    /**
     * Cast a timestamp into a DateTime object with correct timezone.
     * 
     * @param string $date
     * @return DateTime
     */
    public function castTimestamp($date);
}
