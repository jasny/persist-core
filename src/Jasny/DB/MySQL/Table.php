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
     * Default values
     * @var array
     */
    protected $defaults;
    
    /**
     * Primary key field name
     * @var string
     */
    protected $primarykey;
    
    
    /**
     * Get all the default values for this table.
     * 
     * @return array
     */
    public function getDefaults()
    {
        if (isset($this->defaults)) return $this->defaults;
        
        $dopk = !isset($this->primarykey); // Determine the primary key if not statically set
        
        $fields = $this->getDB()->fetchAll("DESCRIBE " . $this->db->backquote($this->getName()), MYSQLI_ASSOC);
        
        $defaults = array();
        foreach ($fields as $field) {
            $value = $field['Default'];
            if (isset($value) && ($field['Type'] == 'date' || $field['Type'] == 'datetime' || $field['Type'] == 'timestamp')) $value = new \DateTime($value == 'CURRENT_TIMESTAMP' ? 'now' : $value);
            $defaults[$field['Field']] = $value;
            
            if ($dopk && $field['Key'] == 'PRI') {
                if (isset($this->primarykey)) {
                    $this->primarykey = (array)$this->primarykey;
                    $this->primarykey[] = $field['Field'];
                } else {
                    $this->primarykey = $field['Field'];
                }
            }
        }
        
        $this->defaults = $defaults;
        return $defaults;
    }
    
    /**
     * Get primary key.
     * 
     * @return string
     */
    public function getIdentifier()
    {
        if (!isset($this->defaults)) $this->getDefaults();
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
}
