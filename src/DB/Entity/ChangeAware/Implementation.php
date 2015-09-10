<?php

namespace Jasny\DB\Entity\ChangeAware;

use SebastianBergmann\Comparator\Factory as ComparatorFactory,
    SebastianBergmann\Comparator\ComparisonFailure;

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
    private $originalData__;

    /**
     * Set the current data as original data
     */
    protected function markAsOriginal()
    {
        $this->originalData__ = $this->getData();
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
        $data = $this->getData();
        
        if ($property === $this) {
            $original = $this->originalData__;
            $current = $data;
        } else {
            $original = isset($this->originalData__[$property]) ? $this->originalData__[$property] : null;
            $current = isset($data[$property]) ? $data[$property] : null;
        }
        
        $factory = new ComparatorFactory();
        $comparator = $factory->getComparatorFor($original, $current);
        
        try {
            $comparator->assertEquals($original, $current);
        } catch (ComparisonFailure $failure) {
            return false;
        }
        
        return true;        
    }
}
