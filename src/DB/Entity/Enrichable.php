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
     * Enrich entity with related data
     * 
     * @return $this
     */
    public function enrich();
}
