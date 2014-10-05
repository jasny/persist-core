<?php

namespace Jasny\DB;

/**
 * Entity can be restored after deletion.
 * 
 * @author  Arnold Daniels <arnold@jasny.net>
 * @license https://raw.github.com/jasny/db/master/LICENSE MIT
 * @link    https://jasny.github.com/db
 */
interface ActiveRecordSoftDeletion extends ActiveRecord, SoftDeletion
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
}
