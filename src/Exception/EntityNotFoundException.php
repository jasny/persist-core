<?php

declare(strict_types=1);

namespace Jasny\DB\Exception;

use OutOfBoundsException;

/**
 * Could not fetch the entity based on specified id or filter
 */
class EntityNotFoundException extends OutOfBoundsException
{
}
