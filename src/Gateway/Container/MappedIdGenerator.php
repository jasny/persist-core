<?php

declare(strict_types=1);

namespace Jasny\DB\Gateway\Container;

use Jasny\DB\Gateway\Container\IdGeneratorInterface;

/**
 * Mapping between Gateway container id and Entity class.
 */
class MappedIdGenerator implements IdGeneratorInterface
{
    /**
     * @var array
     */
    protected $map;

    /**
     * MappedIdGenerator constructor.
     *
     * @param array $map
     */
    public function __construct(array $map)
    {
        $this->map = $map;
    }


    /**
     * Turn a container id to a Entity class
     *
     * @param string $id
     * @return string
     */
    public function fromIdToEntityClass(string $id): string
    {
        return $this->map[$id] ?? '';
    }

    /**
     * Turn a container id to a Gateway class
     *
     * @param string $id
     * @return string
     */
    public function fromIdToGatewayClass(string $id): string
    {
        return isset($this->map[$id]) ? $this->map[$id] . 'Gateway' : '';
    }


    /**
     * Turn an Entity class to a container id
     *
     * @param string $fqcn
     * @return string
     */
    public function fromEntityClassToId(string $fqcn): string
    {
        $id = array_search($fqcn, $this->map, true);

        return $id !== false ? $id : '';
    }

    /**
     * Turn an Entity class to a container id
     *
     * @param string $fqcn
     * @return string
     */
    public function fromEntityClassToGatewayClass(string $fqcn): string
    {
        return $fqcn . 'Gateway';
    }


    /**
     * Turn a Gateway class to a container id
     *
     * @param string $fqcn
     * @return string
     */
    public function fromGatewayClassToId(string $fqcn): string
    {
        $entityClass = $this->fromGatewayClassToEntityClass($fqcn);

        return $this->fromEntityClassToId($entityClass);
    }

    /**
     * Turn a Gateway class to an Entity class
     *
     * @param string $fqcn
     * @return string
     */
    public function fromGatewayClassToEntityClass(string $fqcn): string
    {
        if (!str_ends_with($class, 'Gateway')) {
            throw new UnexpectedValueException("Gateway class should end with 'Gateway'");
        }

        return substr($fqcn, 0, -7);
    }
}
