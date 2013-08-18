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
 * MySQL DB connection.
 * 
 * @todo Create an ArrayObject class `ConnectionPool` for $connections. With a connection pool you should be able to split up a difficult query and distribute it over all the connections.
 * 
 * @example <br/>
 *   use Jasny\DB\MySQL\Connection as DB;<br/>
 *   new DB($host, $user, $pwd, $dbname);<br/>
 *   $result = DB::conn()->query("SELECT * FROM foo WHERE id = ?", $id);
 * 
 * @package MySQL
 */
class Connection extends \mysqli implements \Jasny\DB\Connection
{
    /**
     * Don't update existing records when saving, but ignore them instead.
     * @var boolean
     */
    const SKIP_EXISTING = false;

    /**
     * Named connections
     * @var Connection[]
     */
    protected static $connections = array();
    
    /**
     * Namespace of the Record and Table classes.
     * @var string
     */
    public $modelNamespace;

    /**
     * The execution time of the last query
     * @var float
     */
    public $execution_time;
    
    /**
     * Logger to log queries, errors and more.
     * @var \Psr\Log\LoggerInterface
     */
    protected $logger;
    

    /**
     * Get the default or named DB connection.
     * 
     * @param string $name
     * @return Connection
     */
    public static function conn($name='default')
    {
        if (!isset(self::$connections[$name])) {
            if ($name != 'default') throw new \Exception("MySQL connection '$name' doesn't exist.");
            self::$connections[$name] = new static();
        }
        
        return self::$connections[$name];
    }

    /**
     * Class constructor.
     * 
     * @param string|array $host      Hostname or settings as assoc array
     * @param string       $username
     * @param string       $password
     * @param string       $dbname
     * @param int          $port 
     */
    public function __construct($host = 'localhost', $username = null, $password = null, $dbname = null, $port = null)
    {
        if (!is_scalar($host)) extract((array)$host, EXTR_IF_EXISTS);
        
        parent::__construct($host, $username, $password, $dbname, $port);
        $this->logConnection();
        
        if ($this->connect_error) {
            $this->log('error', $this->connect_error);
            throw new \Exception("Failed to connect to the database: " . $this->connect_error);
        }
        
        if (!isset(self::$connections['default'])) self::$connections['default'] = $this;
        if (!isset(\Jasny\DB\Table::$defaultConnection)) \Jasny\DB\Table::$defaultConnection = $this;
    }
    
    /**
     * Closes a previously opened database connection.
     * 
     * @return boolean
     */
    public function close()
    {
        foreach (array_keys(self::$connections, $this, true) as $name) unset(self::$connections[$name]);
        if (\Jasny\DB\Table::$defaultConnection === $this) \Jasny\DB\Table::$defaultConnection = null;
        
        return parent::close();
    }
    
    /**
     * Name the connection.
     * 
     * @param string $name
     */
    public function useAs($name)
    {
        self::$connections[$name] = $this;
        if ($name === 'default') \Jasny\DB\Table::$defaultConnection = $this;
    }
    
    /**
     * Get the name of the connection.
     * If the connection has multiple names, returns the first one.
     * 
     * @return string
     */
    public function getConnectionName()
    {
        array_search($this, self::$connections, true) ?: null;
    }

    
    /**
     * Performs a query on the database.
     * Don't mix both types ('?' and ':key') of placeholders.
     * 
     * @example DB::conn()->query("SELECT * FROM mytable WHERE id=?", $id);
     * @example DB::conn()->query("SELECT * FROM mytable WHERE name=:name AND age>:age AND status='A'", array('id'=>$id, 'age'=>$age));
     * 
     * @param string $query  SQL Query
     * @return \mysqli_result
     */
    public function query($query)
    {
        if (func_num_args() > 1) $query = call_user_func_array(array(get_class(), 'bind'), func_get_args());

        $time = microtime(true); // We should use profiling to get the execution time, but this is easier
        
        $result = parent::query((string)$query);
        $this->execution_time = microtime(true) - $time;
        
        $this->logQuery((string)$query, $result);
        
        if (!$result) throw new Exception($this->error, $this->errno, $query);
        return $result;
    }
    

    /**
     * Query and fetch all result rows as an associative array, a numeric array, or both.
     * 
     * @example DB::conn()->fetchAll("SELECT * FROM mytable");
     * @example DB::conn()->fetchAll("SELECT * FROM mytable", MYSQLI_NUM);
     * @example DB::conn()->fetchAll("SELECT * FROM mytable WHERE group=?", MYSQLI_ASSOC, $group);
     * @example DB::conn()->fetchAll("SELECT * FROM foobar WHERE group=?", 'FooBar', $group)
     *
     * @param string|\mysqli_result $query       SQL Query or DB result
     * @param int|string            $resulttype  MYSQLI_ASSOC, MYSQLI_NUM, MYSQLI_BOTH or class name
     * @return array
     */
    public function fetchAll($query, $resulttype = MYSQLI_ASSOC)
    {
        if (func_num_args() > 2) {
            $args = func_get_args();
            unset($args[1]);
            $query = call_user_func_array(array(get_class(), 'bind'), array_values($args));
        }

        $result = $query instanceof \mysqli_result ? $query : $this->query($query);

        // Using mysqlnd :)
        if (function_exists('mysqli_fetch_all') && is_int($resulttype)) {
            return $result->fetch_all($resulttype);
        }

        // We don't have it :( or we're fetching objects
        $rows = array();
        if (is_string($resulttype)) {
            while ($row = $result->fetch_object($resulttype)) $rows[] = $row;
        } else {
            while ($row = $result->fetch_array($resulttype)) $rows[] = $row;
        }

        return $rows;
    }

    /**
     * Query and fetch all result rows as an associative array, a numeric array, or both.
     * 
     * @example DB::conn()->fetchOne("SELECT * FROM mytable");
     * @example DB::conn()->fetchOne("SELECT * FROM mytable", MYSQLI_NUM);
     * @example DB::conn()->fetchOne("SELECT * FROM mytable WHERE id=?", MYSQLI_ASSOC, $id);
     * @example DB::conn()->fetchOne("SELECT * FROM foobar WHERE id=?", 'FooBar', $id);
     *
     * @param string|\mysqli_result $query       SQL Query or DB result
     * @param int|string            $resulttype  MYSQLI_ASSOC, MYSQLI_NUM, MYSQLI_BOTH or class name
     * @return array
     */
    public function fetchOne($query, $resulttype = MYSQLI_ASSOC)
    {
        if (func_num_args() > 2) {
            $args = func_get_args();
            unset($args[1]);
            $query = call_user_func_array(array(get_class(), 'bind'), array_values($args));
        }

        $result = $query instanceof \mysqli_result ? $query : $this->query($query);

        return is_string($resulttype) ? $result->fetch_object($resulttype) : $result->fetch_array($resulttype);
    }

    /**
     * Query and fetch a single column from all result rows.
     * 
     * @example DB::conn()->fetchColumn("SELECT name FROM mytable");
     * @example DB::conn()->fetchColumn("SELECT name FROM mytable WHERE group=?", $group);
     *
     * @param string|\mysqli_result $query  SQL Query or DB result
     * @param string                $field  The field position or name to fetch
     * @return array
     */
    public function fetchColumn($query)
    {
        if (func_num_args() > 1) $query = call_user_func_array(array(get_class(), 'bind'), func_get_args());

        $result = $query instanceof \mysqli_result ? $query : $this->query($query);

        $values = array();
        while (list($value) = $result->fetch_row()) {
            $values[] = $value;
        }

        return $values;
    }

    /**
     * Fetches all result rows and creates an associated array with the first column as key and the second as value.
     * 
     * @example DB::conn()->fetchPairs("SELECT id, name FROM mytable");
     * @example DB::conn()->fetchPairs("SELECT id, name FROM mytable WHERE group=?", $group);
     *
     * @param string|\mysqli_result $query  SQL Query or DB result
     * @return array
     */
    public function fetchPairs($query)
    {
        if (func_num_args() > 1) $query = call_user_func_array(array(get_class(), 'bind'), func_get_args());

        $result = $query instanceof \mysqli_result ? $query : $this->query($query);

        $values = array();
        while (list($key, $value) = $result->fetch_row()) {
            $values[$key] = $value;
        }

        return $values;
    }

    /**
     * Query and fetch a single value.
     * 
     * @example DB::conn()->fetchValue("SELECT SUM(foo) FROM mytable");
     * @example DB::conn()->fetchValue("SELECT name FROM mytable WHERE id=?", $id);
     *
     * @param string|\mysqli_result $query  SQL Query or DB result
     * @param string                $field  The field position or name to fetch
     * @return array
     */
    public function fetchValue($query)
    {
        if (func_num_args() > 1) $query = call_user_func_array(array(get_class(), 'bind'), func_get_args());

        $result = $query instanceof \mysqli_result ? $query : $this->query($query);
        list($value) = $result->fetch_row();

        return $value;
    }

    
    /**
     * Insert or update a record.
     * All rows should have the same keys in the same order.
     * 
     * @example DB::conn()->save('mytable', $row)
     * @example DB::conn()->save('mytable', array($row1, $row2, $row3))
     * @example DB::conn()->save('mytable', array($row1, $row2, $row3), DB::SKIP_EXISTING)
     * 
     * @param string  $table
     * @param array   $values  One or multiple rows of values
     * @param boolean $update  Update on duplicate key
     * @return int  Last insert ID
     */
    public function save($table, array $values = array(), $update = true)
    {
        if (!is_array(reset($values))) $values = array($values); // Make sure that $values is a set of rows

        $fields = array();
        $query_values = array();
        $query_update = array();

        foreach (array_keys(reset($values)) as $key) {
            $field = static::backquote($key);
            $fields[$key] = $field;
            $query_update[$key] = "$field=VALUES($field)";
        }

        foreach ($values as &$row) {
            $vals = array();
            foreach (array_keys($fields) as $key) {
                $vals[$key] = static::quote($row[$key], 'DEFAULT');
            }
            $query_values[] = '(' . join(', ', $vals) . ')';
        }

        $query = "INSERT" . ($update ? '' : " IGNORE") . " INTO `" . str_replace('`', '', $table) . "` (" . join(', ', $fields) . ") VALUES " . join(', ', $query_values) .
                ($update ? " ON DUPLICATE KEY UPDATE " . join(', ', $query_update) : '');

        $this->query($query);
        return $this->insert_id;
    }

    
    /**
     * Quote a value so it can be savely used in a query.
     * 
     * @param mixed  $value
     * @param string $empty  Return $empty if $value is null
     * @return string
     */
    public static function quote($value, $empty = 'NULL')
    {
        return QuerySplitter::quote($value, $empty);
    }

    /**
     * Quotes a string so it can be used as a table or column name.
     * Dots are seen as seperator and are kept out of quotes.
     * 
     * Doesn't quote expressions without Query::BACKQUOTE_STRICT. This means it is not secure without this option. 
     * 
     * @param string   $identifier
     * @param int      $flags       Query::BACKQUOTE_%
     * @return string
     */
    public static function backquote($identifier, $flags = Query::BACKQUOTE_STRICT)
    {
        return QuerySplitter::backquote($identifier, $flags);
    }

    /**
     * Insert parameters into SQL query.
     * Don't mix both types ('?' and ':key') of placeholders.
     * 
     * @example DB::bind("SELECT * FROM mytable WHERE id=?", $id);
     * @example DB::bind("SELECT * FROM mytable WHERE name=:name AND age>:age AND status='A'", array('id'=>$id, 'age'=>$age));
     * 
     * @param string $query
     * @param mixed  $params  Parameters can be passed as indifidual arguments or as array
     * @return string
     */
    public static function bind($query, $params = array())
    {
        if ($query instanceof \mysqli_result) trigger_error("Can only bind on a query not on a query result", E_USER_ERROR);
        
        if (!is_array($params) || is_int(key($params))) {
            $args = func_get_args();
            $params = array_splice($args, 1);
        }
        
        return QuerySplitter::bind($query, $params);
    }
    

    /**
     * Get a table gateway.
     * 
     * @param string $name  Table name
     * @return Table
     */
    public function table($name)
    {
        return Table::factory($name, $this);
    }

    /**
     * Check if table exsists.
     * 
     * @param string $name  Table name
     * @return boolean
     */
    public function tableExists($name)
    {
        return (bool)$this->fetchValue("SHOW TABLES LIKE ?", $name);
    }
    
    /**
     * Get the names of all tables
     * 
     * @return array
     */
    public function getAllTables()
    {
        return $this->fetchColumn("SHOW TABLES");
    }
    
    /**
     * Load a record using the table gateway.
     * 
     * Shortcut for `$db->table($table)->fetch($id)`.
     * 
     * @param string $table  Table name
     * @param mixed  $id
     * @return \Jasny\DB\Record
     */
    public function load($table, $id)
    {
        return $this->table($table)->fetch($id);
    }
    
    
    /**
     * Set the model namespace.
     * 
     * @param string $ns
     */
    public function setModelNamespace($ns)
    {
        $this->modelNamespace = trim($ns, '\\');
    }

    /**
     * Get the model namespace.
     * 
     * @return string
     */
    public function getModelNamespace()
    {
        return $this->modelNamespace;
    }
    
    
    /**
     * Set logger interface to log queries.
     * 
     * Supports PSR-3 compatible loggers (like Monolog).
     * @see https://github.com/php-fig/fig-standards/blob/master/accepted/PSR-3-logger-interface.md
     * @see https://packagist.org/packages/monolog/monolog
     * 
     * @param \Psr\Log\LoggerInterface $logger
     */
    public function setLogger($logger)
    {
        $this->logger = $logger;
        if (@$this->server_info) $this->logConnection();
    }
    
    /**
     * Log connection info
     */
    protected function logConnection()
    {
        if (isset($this->logger)) $this->logger->debug("MySQL connection {$this->host_info}; thread id = {$this->thread_id}; version {$this->server_info}");
    }
    
    /**
     * Log a query.
     * 
     * @param string        $query
     * @param mysqli_result $result
     */
    protected function logQuery($query, $result)
    {
        if (empty($this->logger)) return;
        
        if ($this->info) $info = " " . $this->info;
         elseif ($result instanceof \mysqli_result) $info = " " . $this->affected_rows . ($this->affected_rows == 1 ? " row" : " rows") . " in set";
         elseif ($this->affected_rows >= 0) $info = " " . $this->affected_rows . " affected". ($this->affected_rows == 1 ? " row" : " rows");
         else $info = "";
        
        if (isset($this->logger)) $this->logger->debug(rtrim($query, ';') . "; #$info (" . number_format($this->execution_time, 4) . " sec)");
        if ($this->error) $this->logger->error($this->error);
    }
}
