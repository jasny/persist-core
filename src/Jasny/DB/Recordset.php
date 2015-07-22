<?php

namespace Jasny\DB;

trigger_error("Jasny\DB\Recordset has been renamed to Jasny\DB\Dataset", E_USER_DEPRECATED);

/**
 * Alias for Dataset.
 * @deprecated
 * 
 * @author  Arnold Daniels <arnold@jasny.net>
 * @license https://raw.github.com/jasny/db/master/LICENSE MIT
 * @link    https://jasny.github.com/db
 */
interface Recordset extends Dataset
{ }
