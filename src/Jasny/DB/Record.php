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
     * Table gateway
     * @var Table
     */
    private $_dbtable;
    
    
    /**
     * Set value for auto_increment field.
     * 
     * @param int $id
     */
    public function setId($id)
    {
        $field = $this->getDBTable()->getIdentifier();
        $this->$field = $id;
    }
    
    /**
     * Get the values of this model.
     * 
     * @return array
     */
    public function getValues()
    {
        $values = (array)$this;
        
        foreach ($values as $key=>&$value) {
            if ($key[0] == "\0"){
                unset($values[$key]);
                continue;
            }
            
            if ($values instanceof DateTime) $value = $value->format('c');
             elseif ($value instanceof static) $value = $value->id;
             elseif (is_object($value) && method_exists($value, '__toString')) $value = (string)$value;
             elseif (!is_scalar($value) && !is_null($value)) unset($values[$key]);
        }
        
        return $values;
    }
    
    /**
     * Set the values of this model.
     * 
     * @param array $values  Array with values (or object with method 'getValues')
     * @return Record  $this
     */
    public function setValues($values)
    {
        if (is_object($values)) $values = method_exists($values, 'getValues') ? $values->getValues() : (array)$values;
        
        foreach (array_keys((array)$this) as $key) {
            if ($key[0] == "\0") continue; // Skip private and protected properties
            if (isset($values[$key])) $this->$key = $values[$key];
        }
        
        return $this;
    }
    
    /**
     * Save the model to the DB.
     */
    public function save()
    {
        $this->getDBTable()->save($this);
    }
    
    
    /**
     * Set the table gateway.
     * @ignore
     * 
     * @param Table $table
     */
    public function _setDBTable($table)
    {
        if (!isset($this->_dbtable)) $this->_dbtable = $table;
    }
    
    /**
     * Get the table gateway.
     * 
     * @return Table
     */
    public function getDBTable()
    {
        if (!isset($this->_dbtable)) $this->_dbtable = Table::factory(get_class($this));
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
}
