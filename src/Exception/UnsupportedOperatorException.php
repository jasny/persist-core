<?php

declare(strict_types=1);

namespace Jasny\DB\Exception;

/**
 * Thrown if an operator isn't supported for this filter or database.
 */
class UnsupportedOperatorException extends \LogicException
{
}
