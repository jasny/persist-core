<?php
/**
 * Jasny DB - A DB layer for the masses.
 * 
 * @author  Arnold Daniels <arnold@jasny.net>
 * @license https://raw.github.com/jasny/db/master/LICENSE MIT
 * @link    https://jasny.github.io/db
 */
/** */
namespace Jasny\DB;

/**
 * Base class for table gateways.
 */
abstract class Table
{
    /** Option to skip check if class exists on Table::getClass() */
    const SKIP_CLASS_EXISTS = 1;


    /**
     * Default database connection
     * @var Connection
     */
    public static $defaultConnection;
    
    /**
     * Created table gateways
     * @var array
     */
    private static $tables = array();
    
    
    /**
     * @var DB
     */
    protected $db;

    /**
     * @var string
     */
    protected $name;

    
    /**
     * Get the default database (with respect of the namespace).
     * 
     * @return Connection
     */
    public static function getDefaultConnection()
    {
        $class = get_called_class();
        
        while ($class != __CLASS__) {
            $ns = preg_replace('/[^\\\\]+$/', '', $class);
            if (class_exists($ns . 'Connection') && is_a($ns . 'Connection', 'Jasny\DB\Connection', true)) {
                return call_user_func(array($ns . 'Connection', 'conn'));
            }
            
            $class = get_parent_class($class);
        };
        
        if (!isset(self::$defaultConnection)) throw new \Exception("Default connection not set, please connect to a DB.");
        return self::$defaultConnection;
    }
    
    /**
     * Get the default Table class (with respect of the namespace).
     * 
     * @param string     $base  The classname we're looking for
     * @param Connection $db
     * @return string
     */
    public static function getDefaultClass($base, $db=null)
    {
        $class = get_class($db ?: static::getDefaultConnection());
        
        do {
            $ns = preg_replace('/[^\\\\]+$/', '', $class);
            if (class_exists($ns . $base) && is_a($ns . $base, __NAMESPACE__ . '\\' . $base, true)) return $ns . $base;
            
            $class = get_parent_class($class);
        } while ($class);
        
        return null;
    }

    
    /**
     * Replacement for `new Table()`.
     * @ignore
     * 
     * {@internal Setting the name is only required if no specific table gateway exists for a table}}
     * 
     * @param string     $name
     * @param Connection $db
     * @return Table
     */
    public static function instantiate($name, Connection $db=null)
    {
        $table = new static($db);
        $table->name = $name;
        
        return $table;
    }
    
    /**
     * Get a table gateway.
     * 
     * @param string $name   Table name or record class name
     * @param DB     $db     Database connection
     * @return Table
     */
    public static function factory($name, Connection $db=null)
    {
        // Find out which class to use (and possibly get the table gateway from cache)
        $name = static::uncamelcase(preg_replace('/^.+\\\\/', '', $name));
        
        if (!isset($db)) $db = self::getDefaultConnection();
        
        $class = ltrim($db->getModelNamespace() . '\\', '\\') . static::camelcase($name) . 'Table';
        if (!class_exists($class)) $class = static::getDefaultClass('Table', $db);
        if (!isset($class)) trigger_error("Table gateways aren't supported for " . get_class($db), E_USER_ERROR);

        $dbname = $db->getConnectionName();
        if (isset($dbname) && isset(self::$tables[$dbname][$name])) {
            // Return cached gateway, only if the modelNamespace hasn't changed.
            $table = self::$tables[$dbname][$name];
            if (get_class($table) == $class) return $table;
        }
        
        $table = $class::instantiate($name, $db);
        self::$tables[$dbname][$name] = $table;
        
        return $table;
    }
    
    
    /**
     * Class constructor.
     * Protected because the factory method should be used.
     * 
     * @param Connection $db    Database connection
     */
    protected function __construct(Connection $db=null)
    {
        $this->db = $db ?: self::getDefaultConnection();
    }
    
    /**
     * Return DB connection
     * 
     * @return Connection
     */
    public function getDB()
    {
        return $this->db;
    }

    /**
     * Get database name
     * 
     * @return string
     */
    public function getName()
    {
        if (!isset($this->name)) {
            $this->name = static::uncamelcase(preg_replace('/^.+\\\\|Table$/i', '', get_class($this)));
        }
        
        return $this->name;
    }
    
    /**
     * Return record class name
     * 
     * @param int $options
     * @return string
     */
    public function getClass($options=0)
    {
        $class = ltrim($this->getDB()->getModelNamespace() . '\\', '\\') . static::camelcase($this->getName());
        
        if ($options & self::SKIP_CLASS_EXISTS) return $class;
        
        return class_exists($class) && is_a($class, __NAMESPACE__ . '\Record', true)
            ? $class
            : self::getDefaultClass('Record', $this->getDB()) ?: __NAMESPACE__ . '\Record';
    }
    
    
    /**
     * Get all the default values for this table.
     * 
     * @return array
     */
    public function getDefaults()
    {
        $defaults = $this->getFieldDefaults();
        $types = $this->getFieldTypes();
        $values = array();
        
        foreach ($defaults as $field=>$value) {
            $values[$field] = static::castValue($value, $types[$field]);
        }
        
        return $values;
    }
    
    /**
     * Get all the default value for each field for this table.
     * 
     * @return array
     */
    abstract public function getFieldDefaults();
    
    /**
     * Get the php type for each field of this table.
     * 
     * @return array
     */
    abstract public function getFieldTypes();
    
    /**
     * Get the property (or properties) to uniquely identifies a record.
     * 
     * @return string|array
     */
    abstract public function getPrimarykey();
    
    
    /**
     * Fetch all records of the table.
     * 
     * @param array $filter  Filter as [ expression, field => value, ... ]
     * @return array
     */
    abstract public function fetchAll(array $filter=array());
    
    /**
     * Load a record from the DB
     * 
     * @param int|array $id  ID or filter
     * @return Record
     */
    abstract public function fetch($id);
    
    /**
     * Fetch a single value from the DB
     * 
     * @param string    $field  Field name
     * @param int|array $id     ID or filter
     * @return mixed
     */
    abstract public function fetchValue($field, $id);
    
    
    /**
     * Cast values to their proper type.
     * 
     * @param Record|array $record  Record, value object or array with values
     * @return Record|array
     */
    public function cast($record)
    {
        $types = $this->getFieldTypes();
        
        foreach ($record as $field=>&$value) {
            if (!isset($types[$field])) continue;
            $value = static::castValue($value, $types[$field]);
        }

        return $record;
    }
    
    
    /**
     * Create a new record.
     * {@internal You can't overwrite this function. Put the code in the contructor of the table's Record class}}
     * 
     * @return Record
     */
    public final function newRecord()
    {
        $class = $this->getClass();
        $record = new $class();
        
        // Add fields for a generic record object
        if (preg_replace('/^.*\\\\/', $class) != self::camelcase($this->getName())) {
            foreach ($this->getDefaults() as $key=>$value) {
                $record->$key = $value;
            }
        }
        
        $record->_setDBTable($this);
        return $record;
    }
    
    /**
     * Save the record to the DB.
     * 
     * @param Record|array $record  Record or array with values
     * @return mixed  id
     */
    abstract public function save($record);

    
    /**
     * Turn a string using underscores in a camelcase string.
     * 
     * @param string $string
     * @return string
     */
    public static function camelcase($string)
    {
        return str_replace(' ', '', ucwords(str_replace('_', ' ', $string)));
    }
    
    /**
     * Turn a camelcase string in a string using underscores.
     * 
     * @param string $string
     * @return string
     */
    public static function uncamelcase($string)
    {
        return strtolower(preg_replace('/(?<=[a-z])([A-Z])(?![A-Z])/', '_$1', $string));
    }
    
    
    /**
     * Cast table to table name.
     * 
     * @return string
     */
    public function __toString()
    {
        return $this->getName();
    }
    
    
    /**
     * Check if a table exists for the default connection.
     * 
     * @param string $name
     * @return boolean
     */
    public static function exists($name)
    {
        return (bool)static::getDefaultConnection()->tableExists($name);
    }

    
    /**
     * Cast the value to a type
     * 
     * @param mixed   $value
     * @param string  $type
     * @param boolean $obj    Create objects for non-internal types
     * @return mixed
     */
    public static function castValue($value, $type, $obj=true)
    {
        if (is_null($value) || (is_object($value) && is_a($value, $type)) || gettype($value) == $type) return $value; // No casting needed
        
        switch ($type) {
            case 'bool': case 'boolean':
            case 'int':  case 'integer':
            case 'float':
            case 'string':
                settype($value, $type);
                break;
                
            case 'array':
                $value = explode(',', $value);
                break;
            
            default:
                if (!$obj) break;
                
                if (!class_exists($type)) throw new \Exception("Invalid type '$type'");
                $value = new $type($value);
                break;
        }
        
        return $value;
    }
}
