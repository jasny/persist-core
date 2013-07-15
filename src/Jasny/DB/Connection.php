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
     * Check if table exsists.
     * 
     * @param string $name  Table name
     * @return boolean
     */
    public function tableExists($name);

    /**
     * Get the names of all tables
     * 
     * @return array
     */
    public function getAllTables();
    
    
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
     * Set logger interface to log queries, errors and more.
     * 
     * Supports PSR-3 compatible loggers (like Monolog).
     * @see https://github.com/php-fig/fig-standards/blob/master/accepted/PSR-3-logger-interface.md
     * @see https://packagist.org/packages/monolog/monolog
     * 
     * @param Psr\Log\LoggerInterface $logger
     */
    public function setLogger($logger);
}
