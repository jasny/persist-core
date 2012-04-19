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
class DB extends mysqli
{
    /**
     * Don't update existing records when saving, but ignore them instead.
     * {@internal Just som syntax candy }}
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
        // If you have a global configuration, you could auto connect
        /*
        global $config;
        if (!isset(self::$connection)) new static($config['db']['host'], $config['db']['username'], $config['db']['password'], $config['db']['dbname'], $config['db']['port']);
        */
        
        if (!isset(self::$connection)) throw new DB_Exception("No DB connection has been made");
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
    public function __construct($host, $username, $passwd, $dbname, $port=null)
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
     * @return mysqli_result
     */
    public function query($query, $params=array())
    {
        if (!empty($params)) $query = call_user_func_array(array(__NAMESPACE__ . '\\DB', 'parse'), func_get_args());
        
        $result = parent::query($query);
        if (!$result) throw new DB_Exception($this->error, $this->errno, $query);

        return $result;
    }
    
    /**
     * Insert or update a record.
     * All rows should have the same keys.
     * 
     * @example $db->save('mytable', $row)
     * @example $db->save('mytable', array($row1, $row2, $row3))
     * 
     * @param string  $table
     * @param array   $values   One or multiple rows of values
     * @param boolean $update   Update on duplicate key
     * @return int  Last insert ID
     */
    public function save($table, array $values=array(), $update=true)
    {
        if (!is_array(reset($values))) $values = array($values); // Make sure that $values is a set of rows
        
        $fields = array();
        $query_values = array();
        $query_update = array();
        
        foreach (array_keys(reset($values)) as $key) {
            $field = DB::backquote($key);
            $fields[$key] = $field;
            $query_update[$key] = "$field=VALUES($field)";
        }

        foreach ($values as &$row) {
            $vals = array();
            foreach (array_keys($fields) as $key) {
                $vals[$key] = DB::quote($row[$key], 'DEFAULT');
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
	public static function quote($value, $empty='NULL')
	{
        if (is_array($value)) return '(' . join(', ', array_map(array(get_class(), 'quote'), $value)) . ')';
        
		if (is_null($value)) return $empty;
		if (is_bool($value)) return $value ? 'TRUE' : 'FALSE';
		if (is_int($value) || is_float($value)) return (string)$value;		
		return '"' . strtr((string)$value, array('\\'=>'\\\\', "\0"=>'\\0', "\r"=>'\\r', "\n"=>'\\n', '"'=>'\\"')) . '"';
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
     * @example DB::parse("SELECT * FROM mytable WHERE id=?", $id);
     * @example DB::parse("SELECT * FROM mytable WHERE name=:name AND age>:age AND status='A'", array('id'=>$id, 'age'=>$age));
     * 
     * @param string $query
     * @param mixed  $params    Parameters can be passed as indifidual arguments or as array
     * @return string
     */
    public static function parse($query, $params=array())
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