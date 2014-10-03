<?php

namespace Jasny\DB;

/**
 * Entity can be restored after deletion.
 */
interface SoftDeletion extends Deletable
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
