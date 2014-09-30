<?php

namespace Jasny\DB;

/**
 * Entity has a unique identifier
 * 
 * @author  Arnold Daniels <arnold@jasny.net>
 * @license https://raw.github.com/jasny/db/master/LICENSE MIT
 * @link    https://jasny.github.com/db
 */
interface Identified
{
    /**
     * Get entity id.
     * 
     * @return mixed
     */
    abstract public function getId();
}
