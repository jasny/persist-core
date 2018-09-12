<?php

declare(strict_types=1);

namespace Jasny\DB\Gateway;

/**
 * Implementation of the prototype interface for different sources.
 */
interface PrototypeInterface
{
    /**
     * Create a copy for a specific source.
     *
     * @param mixed $source
     * @return static
     */
    public function withSource($source);
}
