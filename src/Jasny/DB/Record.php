<?php
/**
 * A basic DB layer for using MySQL.
 * 
 * PHP version 5.3+
 * 
 * @author  Arnold Daniels <arnold@jasny.net>
 * @license https://raw.github.com/jasny/DB-MySQL/master/LICENSE MIT
 * @link    https://jasny.github.com/DB-MySQL
 */
/** */
namespace Jasny\DB;

/**
 * DB Record
 */
class Record
{
    /**
     * Table gateway.
     * @var Table
     */
    protected $_dbtable;
    
    
    /**
     * Get the value of the identifier field.
     * 
     * @param int $id
     */
    public function getId()
    {
        $field = $this->getDBTable()->getPrimarykey();
        if (!$field) throw new \Exception("Table " . $this->getDBTable()->getName() . " does not have an identifier field");
        
        // Composite ID
        if (is_array($field)) {
            $id = array();
            foreach ($field as $f) $id[$f] = $this->$f;
            return $id;
        }
        
        // Single ID field
        return $this->$field;
    }
    
    /**
     * Set a value for the identifier field.
     * 
     * @param int $id
     */
    public function setId($id)
    {
        $field = $this->getDBTable()->getPrimarykey();
        if (!$field) throw new \Exception("Table " . $this->getDBTable()->getName() . " does not have an identifier field");
        if (is_array($field)) throw new \Exception("Table " . $this->getDBTable()->getName() . " has a composite identifier field");
        
        $this->$field = $id;
    }
    
    /**
     * Returns the values of all public properties of the Record.
     * 
     * @return array
     */
    public function getValues()
    {
        $values = (array)$this;
        
        foreach ($values as $key=>&$value) {
            if ($key[0] == "\0") unset($values[$key]);
        }
        
        return $values;
    }
    
    /**
     * Set the values of this Record.
     * 
     * @param array $values  Array with values or any object with method 'getValues'
     * @return Record  $this
     */
    public function setValues($values)
    {
        if (is_object($values)) $values = method_exists($values, 'getValues') ? $values->getValues() : (array)$values;
        
        foreach (array_keys((array)$this) as $key) {
            if ($key[0] == "\0") continue; // Skip private and protected properties
            if (array_key_exists($key, $values)) $this->$key = $values[$key];
        }
        
        return $this;
    }
    
    /**
     * Cast all properties to a type based on the field types.
     * 
     * @return Record $this
     */
    public function cast()
    {
        return $this->getDBTable()->cast($this);
    }
    
    /**
     * Save the model to the DB.
     * 
     * @return Record $this
     */
    public function save()
    {
        $this->getDBTable()->save($this);
        return $this;
    }
    
    /**
     * Reload all the properties of the record from the DB.
     * Any unsaved changes are discarded.
     * 
     * @return Record $this
     */
    public function reload()
    {
        $record = $this->getDBTable()->fetch($record->getId());
        
        foreach ($new as $key=>$value) {
            $this->$key = $value;
        }
        
        return $this;
    }
    
    
    /**
     * Set the table gateway.
     * @ignore
     * 
     * @param Table $table
     */
    public function _setDBTable($table)
    {
        if (!isset($this->_dbtable)) {
            $this->_dbtable = $table;
            $this->cast();
         }
    }
    
    /**
     * Get the table gateway.
     * 
     * @return Table
     */
    public function getDBTable()
    {
        if (!isset($this->_dbtable)) $this->_dbtable = Table::factory(get_class($this));
         elseif (is_string($this->_dbtable)) $this->_dbtable = Table::factory($this->_dbtable);
         
        return $this->_dbtable;
    }
    
    
    /**
     * Anything that's called staticly, uses the table gateway instead.
     * 
     * @param string $name
     * @param array $arguments
     * @return mixed
     */
    public static function __callStatic($name, $arguments)
    {
        $dbtable = Table::factory(get_called_class());
        return call_user_func_array(array($dbtable, $name), $arguments);
    }
    
    
    /**
     * Forget dbtable when serialized.
     * 
     * @return array
     */
    public function __sleep()
    {
        if (isset($this->_dbtable)) $this->_dbtable = $this->_dbtable->getName();
        return array_keys(get_object_vars($this));
    }
}
