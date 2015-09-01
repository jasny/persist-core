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
     * Purge all deleted entities
     * 
     * @param array $opts
     */
    public static function purgeAll(array $opts = []);
}
