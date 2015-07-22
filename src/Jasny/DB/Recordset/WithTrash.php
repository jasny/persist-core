<?php

namespace Jasny\DB\Recordset;

trigger_error("Jasny\DB\Recordset has been renamed to Jasny\DB\Dataset", E_USER_DEPRECATED);

/**
 * Interface for a recordset that can fetch deleted entities
 * @deprecated
 * 
 * @author  Arnold Daniels <arnold@jasny.net>
 * @license https://raw.github.com/jasny/db/master/LICENSE MIT
 * @link    https://jasny.github.com/db
 */
interface WithTrash extends \Jasny\DB\Dataset\WithTrash
{ }
