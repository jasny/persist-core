<?php
/**
 * Jasny DB - A DB layer for the masses.
 * 
 * @author  Arnold Daniels <arnold@jasny.net>
 * @license https://raw.github.com/jasny/db/master/LICENSE MIT
 * @link    https://jasny.github.io/db
 */
/** */
namespace Jasny\DB\MySQL;

/**
 * MySQL query exception.
 * 
 * @package MySQL
 */
class Exception extends \mysqli_sql_exception implements \Jasny\DB\Exception
{
    /**
     * Failed query
     * @var string
     */
    protected $query;

    /**
     * DB error message
     * @var string
     */
    protected $error;
    
    
    /**
     * Class constructor
     * 
     * @param string    $error  Error message
     * @param int       $code   Error code
     * @param string    $query
     * @param Exception $previous 
     */
    public function __construct($error, $code=0, $query=null, $previous=null)
    {
        $message = (isset($query) && $code ? "Query has failed. $error" : $error) . ($query ? ".\n$query" : '');
        parent::__construct($message, $code, $previous);
        
        $this->query = $query;
        $this->error = $error;
    }
    
    /**
     * Get DB error message
     * 
     * @return string
     */
    public function getError()
    {
        return $this->error;
    }
    
    /**
     * Get failed query
     * 
     * @return string 
     */
    public function getQuery()
    {
        return $this->query;
    }
}