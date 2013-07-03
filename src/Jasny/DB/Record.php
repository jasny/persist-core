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
     * Get the value of the identifier field.
     * 
     * @param int $id
     */
    public function getId()
    {
        $field = $this->getDBTable()->getIdentifier();
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
        $field = $this->getDBTable()->getIdentifier();
        if (!$field) throw new \Exception("Table " . $this->getDBTable()->getName() . " does not have an identifier field");
        if (is_array($field)) throw new \Exception("Table " . $this->getDBTable()->getName() . " has a composite identifier field");
        
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
            
            if ($value instanceof \DateTime) $value = $value->format('c');
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
     * Cast a timestamp into a DateTime object with correct timezone.
     * 
     * @param string $date
     * @return DateTime
     */
    public final function castTimestamp($date)
    {
        return $this->getDBTable()->getDB()->castTimestamp($date);
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
