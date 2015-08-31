<?php

namespace Jasny\DB;

/**
 * Interface for any DB connection.
 * 
 * @author  Arnold Daniels <arnold@jasny.net>
 * @license https://raw.github.com/jasny/db/master/LICENSE MIT
 * @link    https://jasny.github.io/db
 */
interface Connection
{
    /**
     * Class constructor
     * 
     * @param array|object $settings
     */
    public function __construct($settings);
}
