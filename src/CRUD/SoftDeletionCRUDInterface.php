<?php

declare(strict_types=1);

namespace Jasny\DB\CRUD;

use Jasny\Entity\EntityInterface;

/**
 * CRUD service that supports soft deletion and restore.
 */
interface SoftDeletionCRUDInterface extends CRUDInterface
{
    /**
     * Checks if entity has been deleted
     * 
     * @param EntityInterface $entity
     * @return bool
     */
    public function isDeleted(EntityInterface $entity): bool;
    
    /**
     * Restore deleted entity.
     * Does nothing is entity isn't deleted.
     * 
     * @param EntityInterface $entity
     * @return void
     */
    public function undelete(EntityInterface $entity): void;
    
    /**
     * Purge deleted entity.
     * 
     * @param EntityInterface $entity
     * @return void
     * @throws \EntityConstraintException  if entity isn't be deleted
     */
    public function purge(EntityInterface $entity): void;
}
