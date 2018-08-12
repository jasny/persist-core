<?php

declare(strict_types=1);

namespace Jasny\DB\Exception;

use OutOfBoundsException;
use Psr\Container\NotFoundExceptionInterface;

/**
 * Exception if gateway doesn't exist.
 */
class GatewayNotFoundException extends OutOfBoundsException implements NotFoundExceptionInterface
{
}