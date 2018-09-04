<?php

declare(strict_types=1);

namespace Jasny\DB;

use Jasny\DB\CRUD\SoftDeletionInterface;

/**
 * Gateway to a data set, like a DB table (RDBMS) or collection (NoSQL).
 */
interface GatewayInterface extends CRUDInterface, FetchInterface, SearchInterface
{
}
