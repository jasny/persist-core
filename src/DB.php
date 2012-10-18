<?php

/**
 * A very simple class for using MySQL.
 * 
 * @author Arnold Daniels
 */
/** */
require_once __DIR__ . '/DB/Exception.php';

/**
 * MySQL DB connection.
 * 
 * { @example
 *   new DB($host, $user, $pwd, $dbname);
 *   $result = DB::conn()->query("SELECT * FROM foo WHERE id = ?", $id);
 * }}
 * 
 * @package DB
 */
class DB extends \mysqli
{
    /**
     * Don't update existing records when saving, but ignore them instead.
     * 
     * @var boolean
     */
    const SKIP_EXISTING = false;

    /**
     * Created DB connection
     */
    protected static $connection = array();

    /**
     * Get the DB connection.
     * 
     * @return DB
     */
    public static function conn()
    {
        // Auto connect using Jasny's Config class
        if (!isset(self::$connection)) {
            if (class_exists('Config') && isset(Config::i()->db)) new static(Config::i()->db['host'], Config::i()->db['username'], Config::i()->db['password'], Config::i()->db['dbname'], isset(Config::i()->db['port']) ? Config::i()->db['port'] : null);
            else throw new DB_Exception("Unable to create DB connection: not configured");
        }

        return self::$connection;
    }

    /**
     * Class constructor.
     * 
     * @param string $host
     * @param string $username
     * @param string $passwd
     * @param string $dbname
     * @param int    $port 
     */
    public function __construct($host, $username, $passwd, $dbname, $port = null)
    {
        parent::__construct($host, $username, $passwd, $dbname, $port);
        $this->set_charset('utf8');

        self::$connection = $this;
    }

    
    /**
     * Performs a query on the database.
     * Don't mix both types ('?' and ':key') of placeholders.
     * 
     * @example DB::conn()->query("SELECT * FROM mytable WHERE id=?", $id);
     * @example DB::conn()->query("SELECT * FROM mytable WHERE name=:name AND age>:age AND status='A'", array('id'=>$id, 'age'=>$age));
     * 
     * @param string $query
     * @param mixed  $params   Parameters can be passed as indifidual arguments or as array
     * @return DB_Result
     */
    public function query($query, $params = array())
    {
        if (func_num_args() > 1) $query = call_user_func_array(array(get_class(), 'bind'), func_get_args());

        $result = parent::query($query);
        if (!$result) throw new DB_Exception($this->error, $this->errno, $query);

        return $result;
    }
    

    /**
     * Query and fetch all result rows as an associative array, a numeric array, or both.
     * 
     * @example DB::conn()->fetchAll("SELECT * FROM mytable");
     * @example DB::conn()->fetchAll("SELECT * FROM mytable", MYSQLI_NUM);
     * @example DB::conn()->fetchAll("SELECT * FROM mytable WHERE group=?", MYSQLI_ASSOC, $group);
     *
     * @param string|mysqli_result $query       SQL Query or DB result
     * @param int                  $resulttype  MYSQLI_ASSOC, MYSQLI_NUM, or MYSQLI_BOTH
     * @return array
     */
    public function fetchAll($query, $resulttype = MYSQLI_ASSOC)
    {
        if (func_num_args() > 2) {
            $args = func_get_args();
            unset($args[1]);
            $query = call_user_func_array(array(get_class(), 'bind'), array_values($args));
        }

        $result = $query instanceof mysqli_result ? $query : $this->query($query);

        // Using mysqlnd :)
        if (function_exists('mysqli_fetch_all')) {
            return $result->fetch_all($resulttype);
        }

        // We don't have it :(
        $rows = array();
        if ($resulttype == MYSQLI_ASSOC) {
            while ($row = $result->fetch_assoc()) $rows[] = $row;
        } elseif ($resulttype == MYSQLI_BOTH) {
            while ($row = $result->fetch_array()) $rows[] = $row;
        } else {
            while ($row = $result->fetch_row()) $rows[] = $row;
        }

        return $rows;
    }

    /**
     * Query and fetch a single column from all result rows.
     * 
     * @example DB::conn()->fetchColumn("SELECT name FROM mytable");
     * @example DB::conn()->fetchColumn("SELECT name FROM mytable WHERE group=?", $group);
     *
     * @param string|mysqli_result $query   SQL Query or DB result
     * @param string               $field   The field position or name to fetch
     * @return array
     */
    public function fetchColumn($query)
    {
        if (func_num_args() > 1) $query = call_user_func_array(array(get_class(), 'bind'), func_get_args());

        $result = $query instanceof mysqli_result ? $query : $this->query($query);

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
     * @param string|mysqli_result $query   SQL Query or DB result
     * @return array
     */
    public function fetchPairs($query)
    {
        if (func_num_args() > 1) $query = call_user_func_array(array(get_class(), 'bind'), func_get_args());

        $result = $query instanceof mysqli_result ? $query : $this->query($query);

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
     * @param string|mysqli_result $query   SQL Query or DB result
     * @param string               $field   The field position or name to fetch
     * @return array
     */
    public function fetchValue($query)
    {
        if (func_num_args() > 1) $query = call_user_func_array(array(get_class(), 'bind'), func_get_args());

        $result = $query instanceof mysqli_result ? $query : $this->query($query);
        list($value) = $result->fetch_row();

        return $value;
    }

    
    /**
     * Insert or update a record.
     * All rows should have the same keys in the same order.
     * 
     * @example $db->save('mytable', $row)
     * @example $db->save('mytable', array($row1, $row2, $row3))
     * @example $db->save('mytable', array($row1, $row2, $row3), DB::SKIP_EXISTING)
     * 
     * @param string  $table
     * @param array   $values   One or multiple rows of values
     * @param boolean $update   Update on duplicate key
     * @return int  Last insert ID
     */
    public function save($table, array $values = array(), $update = true)
    {
        if (!is_array(reset($values))) $values = array($values); // Make sure that $values is a set of rows

        $fields = array();
        $query_values = array();
        $query_update = array();

        foreach (array_keys(reset($values)) as $key) {
            $field = self::backquote($key);
            $fields[$key] = $field;
            $query_update[$key] = "$field=VALUES($field)";
        }

        foreach ($values as &$row) {
            $vals = array();
            foreach (array_keys($fields) as $key) {
                $vals[$key] = self::quote($row[$key], 'DEFAULT');
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
     * @param mixed $value
     * @param string $empty  Return $empty if $value is null
     * @return string
     */
    public static function quote($value, $empty = 'NULL')
    {
        if (is_array($value)) return '(' . join(', ', array_map(array(get_class(), 'quote'), $value)) . ')';

        if (is_null($value)) return $empty;
        if (is_bool($value)) return $value ? 'TRUE' : 'FALSE';
        if (is_int($value) || is_float($value)) return (string)$value;
        return '"' . strtr((string)$value, array('\\' => '\\\\', "\0" => '\\0', "\r" => '\\r', "\n" => '\\n', '"' => '\\"')) . '"';
    }

    /**
     * Quote a field, table or dbname so it can be savely used in a query.
     * 
     * @param string $field
     * @return string
     */
    public static function backquote($field)
    {
        return '`' . str_replace('`', '', $field) . '`';
    }

    /**
     * Insert parameters into SQL query.
     * Don't mix both types ('?' and ':key') of placeholders.
     * 
     * @example DB::bind("SELECT * FROM mytable WHERE id=?", $id);
     * @example DB::bind("SELECT * FROM mytable WHERE name=:name AND age>:age AND status='A'", array('id'=>$id, 'age'=>$age));
     * 
     * @param string $query
     * @param mixed  $params    Parameters can be passed as indifidual arguments or as array
     * @return string
     */
    public static function bind($query, $params = array())
    {
        if (!is_array($params) || is_int(key($params))) $params = array_splice(func_get_args(), 1);

        $fn = function ($match) use (&$params) {
            if (!empty($match[1]) && !empty($params)) return DB::quote(array_shift($params));
            if (!empty($match[2]) && array_key_exists($match[2], $params)) return DB::quote($params[$match[2]]);
            return $match[0];
        };

        return preg_replace_callback('/`[^`]*+`|"(?:[^"\\\\]++|\\\\.)*+"|\'(?:[^\'\\\\]++|\\\\.)*+\'|(\?)|:(\w++)/', $fn, $query);
    }
}
