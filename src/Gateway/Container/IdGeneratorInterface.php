<?php

declare(strict_types=1);

namespace Jasny\DB\Gateway\Container;

/**
 * Generate container id from class name and visa versa.
 */
interface IdGeneratorInterface
{
    /**
     * Turn a container id to a Entity class
     *
     * @param string $id
     * @return string
     */
    public function fromIdToEntityClass(string $id): string;

    /**
     * Turn a container id to a Gateway class
     *
     * @param string $id
     * @return string
     */
    public function fromIdToGatewayClass(string $id): string;


    /**
     * Turn an Entity class to a container id
     *
     * @param string $fqcn
     * @return string
     */
    public function fromEntityClassToId(string $fqcn): string;

    /**
     * Turn an Entity class to a Gateway class
     *
     * @param string $fqcn
     * @return string
     */
    public function fromEntityClassToGatewayClass(string $fqcn): string;


    /**
     * Turn a Gateway class to a container id
     *
     * @param string $fqcn
     * @return string
     */
    public function fromGatewayClassToId(string $fqcn): string;

    /**
     * Turn a Gateway class to an Entity class
     *
     * @param string $fqcn
     * @return string
     */
    public function fromGatewayClassToEntityClass(string $fqcn): string;
}
