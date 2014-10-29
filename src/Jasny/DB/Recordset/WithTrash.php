<?php

namespace Jasny\DB\Recordset;

/**
 * Interface for a recordset that can fetch deleted entities
 * 
 * @author  Arnold Daniels <arnold@jasny.net>
 * @license https://raw.github.com/jasny/db/master/LICENSE MIT
 * @link    https://jasny.github.com/db
 */
interface WithTrash extends \Jasny\DB\Recordset
{
    /**
     * Fetch a deleted entity.
     * 
     * @param mixed|array $id  ID or filter
     * @return static[]
     */
    public static function fetchDeleted($id);
    
    /**
     * Fetch all deleted entities.
     * 
     * @param array $filter
     * @param array $sort
     * @return static[]
     */
    public static function fetchAllDeleted(array $filter=[], $sort=null);

    /**
     * Count all deleted entities in the collection
     * 
     * @param array $filter
     * @return static[]
     */
    public static function countDeleted(array $filter=[]);
    
    /**
     * Purge all deleted entities
     */
    public static function purgeAll();
}
