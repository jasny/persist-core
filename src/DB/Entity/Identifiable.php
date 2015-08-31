<?php

namespace Jasny\DB\Entity;

/**
 * Entity has a unique identifier
 * 
 * @author  Arnold Daniels <arnold@jasny.net>
 * @license https://raw.github.com/jasny/db/master/LICENSE MIT
 * @link    https://jasny.github.com/db
 */
interface Identifiable extends \Jasny\DB\Entity
{
    /**
     * Get entity id.
     * 
     * @return mixed
     */
    public function getId();
    
    /**
     * Get identity property
     */
    public static function getIdProperty();
}
