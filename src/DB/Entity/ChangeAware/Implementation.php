<?php

namespace Jasny\DB\Entity\ChangeAware;

use SebastianBergmann\Comparator\Factory as ComparatorFactory;
use SebastianBergmann\Comparator\ComparisonFailure;

/**
 * Implementation for change aware entities.
 * 
 * @author  Arnold Daniels <arnold@jasny.net>
 * @license https://raw.github.com/jasny/db-mongo/master/LICENSE MIT
 * @link    https://jasny.github.io/db-mongo
 */
trait Implementation
{
    /**
     * @var array
     */
    private $persistedData__;

    /**
     * Set the current data as persisted data
     */
    protected function markAsPersisted()
    {
        $this->persistedData__ = $this->toData();
    }
    
    /**
     * Get the persisted data
     * 
     * @return array
     */
    protected function getPersistedData()
    {
        return $this->persistedData__;
    }
    
    
    /**
     * Check if the entity is new
     * 
     * @return boolean
     */
    public function isNew()
    {
        return $this->getPersistedData() === null;
    }
    
    /**
     * Check if the entity is modified
     * 
     * @return boolean
     */
    public function isModified()
    {
        return $this->hasModified($this);
    }
    
    /**
     * Check if a property has changed
     * 
     * @param string $property
     * @return boolean
     */
    public function hasModified($property)
    {
        if ($property === $this) {
            $original = $this->getPersistedData();
            $current = $this->toData();
        } else {
            $persisted = static::fromData($this->getPersistedData() ?: []);

            $original = isset($persisted->$property) ? $persisted->$property : null;
            $current = isset($this->$property) ? $this->$property : null;
        }
        
        if ($original === $current) return false;
        
        $factory = new ComparatorFactory();
        $comparator = $factory->getComparatorFor($original, $current);
        
        try {
            $comparator->assertEquals($original, $current);
        } catch (ComparisonFailure $failure) {
            return true;
        }
        
        return false;        
    }
    
    
    
    /**
     * Get the values that have changed
     * 
     * @return array
     */
    public function getChanges()
    {
        $values = [];
        
        foreach ($this->getValues() as $prop => $value) {
            if ($this->hasModified($prop)) $values[$prop] = $value;
        }
        
        return $values;
    }
}
