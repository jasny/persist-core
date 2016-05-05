<?php

namespace Jasny\DB;

use Jasny\DB;

/**
 * Exception when DB isn't properly configured
 */
class ConfigurationException extends \RuntimeException implements DB\Exception
{
}
