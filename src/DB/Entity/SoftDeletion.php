<?php

namespace Jasny\DB\Entity;

/**
 * Entity can be restored after deletion.
 * 
 * @author  Arnold Daniels <arnold@jasny.net>
 * @license https://raw.github.com/jasny/db/master/LICENSE MIT
 * @link    https://jasny.github.com/db
 */
interface SoftDeletion extends \Jasny\DB\Entity\ActiveRecord, \Jasny\DB\SoftDeletion
{
    /**
     * Checks if entity has been deleted
     * 
     * @return boolean
     */
    public function isDeleted();
    
    /**
     * Restore deleted entity.
     * Does nothing is entity isn't deleted.
     * 
     * @return $this
     */
    public function undelete();
    
    /**
     * Purge deleted entity.
     * 
     * @return $this
     * @throws \Exception if entity ism't deleted
     */
    public function purge();
}
