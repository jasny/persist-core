<?php

declare(strict_types=1);

namespace Jasny\DB\Gateway;

use Doctrine\Inflector\CachedWordInflector;
use Doctrine\Inflector\Inflector;
use Doctrine\Inflector\Rules\English;
use Jasny\DB\Gateway\Container\ClassInflector;
use Jasny\DB\Gateway\Container\IdGeneratorInterface;
use Jasny\DB\Gateway\Container\IdInflector;
use Jasny\DB\Gateway\Container\InflectionIdGenerator;
use Jasny\DB\GatewayInterface;
use Jasny\DB\Gateway\Exception\GatewayNotFoundException;
use Jasny\EntityInterface;
use Psr\Container\ContainerInterface;

use function Jasny\expect_type;

/**
 * Gateway cointainer
 */
class Container implements ContainerInterface
{
    /**
     * @var FactoryInterface
     */
    protected $factory;

    /**
     * @var IdGeneratorInterface
     */
    protected $idGenerator;

    /**
     * @var GatewayInterface[]
     */
    protected $gateways = [];


    /**
     * Container constructor.
     *
     * @param FactoryInterface     $factory
     * @param IdGeneratorInterface $idGenerator
     */
    public function __construct(FactoryInterface $factory, IdGeneratorInterface $idGenerator)
    {
        $this->factory = $factory;
        $this->idGenerator = $idGenerator;
    }

    /**
     * Get gateway by name or for Entity
     *
     * @param string|EntityInterface $id
     * @return mixed
     * @throws GatewayNotFoundException
     */
    public function get($id)
    {
        expect_type($id, ['string', EntityInterface::class]);

        if (!is_string($id)) {
            $entityClass = get_class($id);
            $id = $this->entityClassToId($entityClass);
        }

        if (!isset($this->gateways[$id])) {
            $entityClass = $entityClass ?? $this->idToEntityClass($id);

            if (!is_a($entityClass, EntityInterface::class, true)) {
                throw new GatewayNotFoundException(
                    "Unable to create gateway '$id'; $entityClass is not an Entity class"
                );
            }

            $this->gateways[$id] = $this->factory->create($entityClass);
        }

        return $this->gateways[$id];
    }

    /**
     * Check if gateway exists
     *
     * @param string|EntityInterface $id
     * @return bool
     */
    public function has($id)
    {
        expect_type($id, ['string', EntityInterface::class]);

        if (!is_string($id) || isset($this->gateways[$id])) {
            return true;
        }

        $class = $this->idToEntityClass($id);

        return class_exists($class) && is_a($class, GatewayInterface::class, true);
    }
}
