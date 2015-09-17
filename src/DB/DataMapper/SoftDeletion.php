<?php

namespace Jasny\DB\DataMapper;

use Jasny\DB\Entity;

/**
 * Data Mapper interface when entities that can be restored after deletion.
 * 
 * @author  Arnold Daniels <arnold@jasny.net>
 * @license https://raw.github.com/jasny/db/master/LICENSE MIT
 * @link    https://jasny.github.com/db
 */
interface SoftDeletion extends \Jasny\DB\DataMapper, \Jasny\DB\SoftDeletion
{
    /**
     * Checks if entity has been deleted
     * 
     * @param Entity $entity
     * @return boolean
     */
    public static function isDeleted(Entity $entity);
    
    /**
     * Restore deleted entity.
     * Does nothing is entity isn't deleted.
     * 
     * @param Entity $entity
     */
    public static function undelete(Entity $entity);
    
    /**
     * Purge deleted entity.
     * 
     * @param Entity $entity
     * @throws \Exception if entity ism't deleted
     */
    public static function purge(Entity $entity);
}
