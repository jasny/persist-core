<?php
/**
 * A basic DB layer for using MySQL.
 * 
 * PHP version 5.3+
 * 
 * @package Jasny/DB
 * @subpackage MySQL
 * @author  Arnold Daniels <arnold@jasny.net>
 * @license https://raw.github.com/jasny/DB-MySQL/master/LICENSE MIT
 * @link    https://jasny.github.com/DB-MySQL
 */
/** */
namespace Jasny\DB\MySQL;

use \Jasny\DB\Record;

/**
 * DB table gateway.
 * 
 * @package Jasny/DB
 * @subpackage MySQL
 */
class Table extends \Jasny\DB\Table
{
    /**
     * PHP type for each MySQL field type.
     * @var array
     */
    protected static $castTypes = array(
        'bit' => 'integer',
        'bit(1)' => 'boolean',
        'bool' => 'boolean',
        'boolean' => 'boolean',
        'tinyint(1)' => 'boolean',
        'tinyint' => 'integer',
        'smallint' => 'integer',
        'mediumint' => 'integer',
        'int unsigned' => 'integer',
        'int' => 'string',      // Might be bigger that PHP signed integers on 32 bit
        'integer' => 'string',  // "
        'bigint' => 'string',   // "
        'decimal' => 'float',
        'dec' => 'float',
        'numeric' => 'float',
        'fixed' => 'float',
        'float' => 'float',
        'double' => 'float',
        'double precision' => 'float',
        'real' => 'float',
        'date' => 'DateTime',
        'datetime' => 'DateTime',
        'timestamp' => 'DateTime',
        'time' => 'DateTime',
        'year' => 'integer',
        'char' => 'string',
        'varchar' => 'string',
        'binary' => 'string',
        'varbinary' => 'string',
        'tinyblob' => 'string',
        'tinytext' => 'string',
        'blob ' => 'string',
        'text' => 'string',
        'mediumblob' => 'string',
        'mediumtext' => 'string',
        'longblob' => 'string',
        'longtext' => 'string',
        'enum' => 'string',
        'set' => 'array'
    );

    /**
     * Default values
     * @var array
     */
    protected $fieldDefaults;
    
    /**
     * PHP type for each field
     * @var array
     */
    protected $fieldTypes;
    
    /**
     * Primary key field name
     * @var string
     */
    protected $primarykey;
    
    
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
     * Determine default values, field types and indentifier.
     */
    protected function describe()
    {
        $fields = $this->getDB()->fetchAll("DESCRIBE " . $this->db->backquote($this->getName()), MYSQLI_ASSOC);
        
        $fieldDefaults = array();
        $types = array();
        $primarykey = array();
        
        foreach ($fields as $field) {
            $type = self::castType($field['Type']);
            $value = $field['Default'];
            if ($type == 'DateTime' && $value == 'CURRENT_TIMESTAMP') $value = 'now';
            
            $fieldDefaults[$field['Field']] = $value;
            $types[$field['Field']] = $type;
            if ($field['Key'] == 'PRI') $primarykey[] = $field['Field'];
        }
        
        if (!isset($this->fieldDefaults)) $this->fieldDefaults = $fieldDefaults;
        if (!isset($this->fieldTypes)) $this->fieldTypes = $types;
        if (!isset($this->primarykey)) $this->primarykey = count($primarykey) <= 1 ? reset($primarykey) : $primarykey;
    }
    
    /**
     * Get all the default value for each field for this table.
     * 
     * @return array
     */
    public function getFieldDefaults()
    {
        if (!isset($this->fieldDefaults)) $this->describe();        
        return $this->fieldDefaults;
    }

    /**
     * Get the php type for each field of this table.
     * 
     * @return array
     */
    public function getFieldTypes()
    {
        if (!isset($this->fieldTypes)) $this->describe();
        return $this->fieldTypes;
    }
    
    /**
     * Get primary key.
     * 
     * @return string
     */
    public function getPrimarykey()
    {
        if (!isset($this->primarykey)) $this->describe();
        return $this->primarykey ?: null;
    }
    
    
    /**
     * Fetch all records of the table.
     * 
     * @return array
     */
    public function fetchAll()
    {
        $db = $this->getDB();
        $records = $db->fetchAll("SELECT * FROM " . $db->backquote($this->getName()), $this->getClass());
        
        foreach ($records as $record) $record->_setDBTable($this);
        
        return $records;
    }

    /**
     * Build a where expression for an id or filter
     * 
     * @param int|array $id  ID or filter
     * @return string
     */
    protected function buildWhere($id)
    {
        $db = $this->getDB();
        
        if (is_array($id)) {
            $filter = array();
            foreach ($id as $key=>$value) {
                $filter[] = $db->backquote($key) . (isset($value) ? ' = ' . $db->quote($value) : ' IS NULL');
            }
            
            $where = join(' AND ', $filter);
        } elseif (!is_array($this->getPrimarykey())) {
            $where = $db->backquote($this->getPrimarykey()) . " = " . $db->quote($id);
        } else {
            throw new \Exception("No or combined primary key. Please pass a filter as associated array.");
        }
        
        return $where;
    }
    
    /**
     * Load a record from the DB
     * 
     * @param int|array $id  ID or filter
     * @return Record
     */
    public function fetch($id)
    {
        if (!isset($id)) return null;
        
        $query = "SELECT * FROM " . $this->getDB()->backquote($this->getName())
                . " WHERE " . $this->buildWhere($id) . " LIMIT 1";
        
        $record = $this->getDB()->fetchOne($query, $this->getClass());
        if (isset($record)) $record->_setDBTable($this);
        
        return $record;
    }
    
    /**
     * Fetch a single value from the DB
     * 
     * @param string    $field  Field name
     * @param int|array $id     ID or filter
     * @return mixed
     */
    public function fetchValue($field, $id)
    {
        if (!isset($id)) return null;

        $types = $this->getFieldTypes();
        
        $query = "SELECT " . $this->getDB()->backquote($field) . " FROM " . $this->getDB()->backquote($this->getName())
                . " WHERE " . $this->buildWhere($id) . " LIMIT 1";
        
        $value = $this->getDB()->fetchValue($query);
        return static::castValue($value, $types[$field]);
    }

    
    /**
     * Save the record to the DB.
     * 
     * @param Record|array $record  Record or array with values
     * @return Record
     */
    public function save($record)
    {
        $values = $record instanceof Record ? $record->getValues() : (array)$record;
        $values = array_intersect_key($values, $this->getFieldDefaults());
        
        $id = $this->getDB()->save($this->getName(), $values);
        
        if (!$record instanceof Record) {
            $filter = $id ?: $this->getFilterForValues($values);
            if ($filter) $record = $this->fetch($filter);
        }
        
        if ($id && $record) $record->setId($id);
        return $record;
    }
    
    /**
     * Get a filter to fetch a record based on values.
     * 
     * @param array $values
     * @return array
     */
    protected function getFilterForValues($values)
    {
        $pk = $this->getPrimarykey();

        // PK on one field
        if (!is_array($pk)) {
            return isset($values[$pk]) ? array($pk => $values[$pk]) : null;
        }
        
        // PK on multiple fields
        $filter = array();
        foreach ($pk as $field) {
            if (!isset($values[$field])) return null;
            $filter[$field] = $values[$field];
        }
        
        return $filter;
    }
    
    
    /**
     * Get PHP type for MySQL field type
     * 
     * @param string $fieldtype
     * @return string
     */
    protected static function castType($fieldtype)
    {
        for ($i = 0; $i < 3; $i++) {
            switch ($i) {
                case 0: $key = $fieldtype; break;
                case 1: $key = preg_replace('/\s*\(.+?\)/', '', $fieldtype); break;
                case 2: $key = preg_replace('/\s*\(.+$/', '', $fieldtype); break;
            }

            if (isset(self::$castTypes[$key])) return self::$castTypes[$key];
        }
        
        trigger_error("Unknown field type '$fieldtype'", E_USER_NOTICE);
        return 'string';
    }
}
