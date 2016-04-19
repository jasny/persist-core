<?php

namespace Jasny\DB\Entity;

/**
 * Entity can be enriched with related data
 * 
 * @author  Arnold Daniels <arnold@jasny.net>
 * @license https://raw.github.com/jasny/db/master/LICENSE MIT
 * @link    https://jasny.github.com/db
 */
interface Enrichable extends \Jasny\DB\Entity
{
    /**
     * Enrich entity with related data.
     * Returns a clone of $this with the additional data.
     * 
     * @param string[] $properties
     * @return $this
     */
    public function with(...$properties);
    
    /**
     * Remove properties from entity.
     * Returns a clone of $this without the specified properties.
     * 
     * @param string[] $properties
     * @return $this
     */
    public function without(...$properties);
    
    /**
     * Returns a clone of $this with only the specified properties.
     * Enriches with related data if needed.
     * 
     * @param string[] $properties
     * @return $this
     */
    public function withOnly(...$properties);
}
