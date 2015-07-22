<?php

namespace Jasny\DB\Dataset;

/**
 * Interface for a data set that can fetch deleted entities
 * 
 * @author  Arnold Daniels <arnold@jasny.net>
 * @license https://raw.github.com/jasny/db/master/LICENSE MIT
 * @link    https://jasny.github.com/db
 */
interface WithTrash extends \Jasny\DB\Dataset
{
    /**
     * Fetch all deleted documents.
     * 
     * @param array $filter
     * @param array $sort
     * @return static[]
     */
    public static function fetchDeleted(array $filter=[], $sort=null);

    /**
     * Count all deleted documents in the collection
     * 
     * @param array $filter
     * @return static[]
     */
    public static function countDeleted(array $filter=[]);
    
    /**
     * Purge all deleted documents
     */
    public static function purgeAll();
}
