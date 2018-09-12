<?php

declare(strict_types=1);

namespace Jasny\DB;

use Jasny\DB\CRUD\CRUDInterface;
use Jasny\DB\Fetch\FetchInterface;
use Jasny\DB\Search\SearchInterface;

/**
 * Gateway to a data set, like a DB table (RDBMS) or collection (NoSQL).
 */
interface GatewayInterface extends CRUDInterface, FetchInterface, SearchInterface
{
}
