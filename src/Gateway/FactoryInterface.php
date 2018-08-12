<?php

declare(strict_types=1);

namespace Jasny\DB\Gateway;

use Jasny\DB\GatewayInterface;

/**
 * Interface for gateway factory
 */
interface FactoryInterface
{
    /**
     * Create a new gateway by entity name
     *
     * @param string $entityClass
     * @return GatewayInterface
     * @throws GatewayNotFoundException if gateway can't be created
     */
    public function create(string $entityClass): GatewayInterface;
}
