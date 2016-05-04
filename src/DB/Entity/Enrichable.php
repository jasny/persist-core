<?php

namespace Jasny\DB\Entity;

use Jasny\DB\Entity;
use Jasny\DB\Entity\Redactable;

/**
 * Entity can be enriched with related data.
 * 
 * @internal This interface should extend Redactable. This is done for BC.
 * 
 * @author  Arnold Daniels <arnold@jasny.net>
 * @license https://raw.github.com/jasny/db/master/LICENSE MIT
 * @link    https://jasny.github.com/db
 */
interface Enrichable extends Entity, Redactable
{
    /**
     * Enrich entity with related data.
     * Returns a clone of $this with the additional data.
     * 
     * @param string[] $properties
     * @return $this
     */
    public function with(...$properties);
}
