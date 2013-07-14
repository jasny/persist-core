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
        'tinyint' => 'int',
        'smallint' => 'int',
        'mediumint' => 'int',
        'int' => 'string',      // Might be bigger that PHP signed integers on 32 bit
        'integer' => 'string',  // "
        'bigint' => 'string',   // "
        'float' => 'float',
        'double' => 'float',
        'double precision' => 'float',
        'real' => 'float',
        'decimal' => 'float',
        'numeric' => 'float',
        'date' => 'DateTime',
        'datetime' => 'DateTime',
        'timestamp' => 'DateTime',
        'time' => 'DateTime',
        'year' => 'int',
        'char' => 'string',
        'varchar' => 'string',
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
    protected $defaults;
    
    /**
     * PHP type for each field
     * @var array
     */
    protected $fieldTypes;
    
    /**
     * Primary key field name
     * @var string
     */
    protected $primarykey = false;

    
    /**
     * Determine default values, field types and indentifier.
     */
    protected function describe()
    {
        $fields = $this->getDB()->fetchAll("DESCRIBE " . $this->db->backquote($this->getName()), MYSQLI_ASSOC);
        
        $defaults = array();
        $types = array();
        
        foreach ($fields as $field) {
            if ($field['Type'] == 'tinyint(1)') $type = 'boolean';
             else $type = self::$castTypes[preg_replace('/\(.+/', '', $field['Type'])];
            
            $value = $field['Default'];
            if ($type == 'DateTime' && $value == 'CURRENT_TIMESTAMP') $value = 'now';
            
            $defaults[$field['Field']] = self::castValue($value, $type);
            $types[$field['Field']] = $type;
            if ($field['Key'] == 'PRI') $primarykey[] = $field['Field'];
        }
        
        if (!isset($this->defaults)) $this->defaults = $defaults;
        if (!isset($this->fieldTypes)) $this->fieldTypes = $types;
        if ($this->primarykey === false && !isset($primarykey)) $this->primarykey = count($primarykey) == 1 ? reset($primarykey) : $primarykey;
    }
    
    /**
     * Get all the default values for this table.
     * 
     * @return array
     */
    public function getDefaults()
    {
        if (!isset($this->defaults)) $this->describe();
        return $this->defaults;
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
    public function getIdentifier()
    {
        if ($this->primarykey === false) $this->describe();
        return $this->primarykey;
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
     * Load a record from the DB
     * 
     * @param int|array $id  ID or filter
     * @return Record
     */
    public function fetch($id)
    {
        $db = $this->getDB();
        
        if (is_array($id)) {
            $filter = array();
            foreach ($id as $key=>$value) $filter[] = $db->backquote($key) . (isset($value) ? ' = ' . $db->quote($value) : ' IS NULL');
            $where = join(' AND ', $filter);
        } elseif (!is_array($this->getIdentifier())) {
            $where = $db->backquote($this->getIdentifier()) . " = " . $db->quote($id);
        } else {
            throw new \Exception("No or combined primary key. Please pass a filter as associated array.");
        }
        
        $record = $db->fetchOne("SELECT * FROM " . $db->backquote($this->getName()) . " WHERE $where LIMIT 1", $this->getClass());
        if ($record) $record->_setDBTable($this);
        
        return $record;
    }
    
    /**
     * Save the record to the DB.
     * 
     * @param Record|array $record  Record or array with values
     * @return Record
     */
    public function save($record)
    {
        $values = $record instanceof \Jasny\DB\Record ? $record->getValues() : (array)$record;
        $id = $this->getDB()->save($this->getName(), $values);
        
        if (!$record instanceof \Jasny\DB\Record) {
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
        $pk = $this->getIdentifier();

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
     * Get the PHP types for values in the result.
     * Other types should be cast.
     * 
     * @return array
     */
    public static function resultValueTypes()
    {
        return array('string');
    }
}
