<?php

namespace Jasny\DB\Entity;

use Jasny\DB\Entity;

/**
 * Entity can censor properties for output
 */
interface Redactable extends Entity
{
    /**
     * Check if a propery is censored
     * 
     * @param string $property
     * @return boolean
     */
    public function hasCensored($property);
    
    /**
     * Censor properties from entity.
     * 
     * @param string[] $properties
     * @return $this
     */
    public function without(...$properties);
    
    /**
     * Censor all  only the specified properties.
     * Enriches with related data if needed.
     * 
     * @param string[] $properties
     * @return $this
     */
    public function withOnly(...$properties);
}
