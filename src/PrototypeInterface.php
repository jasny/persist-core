<?php

declare(strict_types=1);

namespace Jasny\DB;

/**
 * Interface for any service that can be created without a source and prototyped.
 */
interface PrototypeInterface
{
    /**
     * Get a copy of the service with a source.
     *
     * @param mixed $source
     * @return static
     */
    public function withSource($source);
}
