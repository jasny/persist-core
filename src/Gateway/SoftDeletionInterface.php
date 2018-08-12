<?php

namespace Jasny\DB\Gateway;

/**
 * Gateway interface for entities that can be restored after deletion.
 */
interface SoftDeletionInterface
{
    /**
     * Checks if entity has been deleted
     * 
     * @param Entity $entity
     * @return bool
     */
    public function isDeleted(Entity $entity): bool;
    
    /**
     * Restore deleted entity.
     * Does nothing is entity isn't deleted.
     * 
     * @param Entity $entity
     * @return void
     */
    public function undelete(Entity $entity): void;
    
    /**
     * Purge deleted entity.
     * 
     * @param Entity $entity
     * @return void
     * @throws \Exception if entity can't be deleted
     */
    public function purge(Entity $entity): void;

    /**
     * Purge all deleted entities.
     *
     * @return void
     */
    public function purgeAll(): void;
}
