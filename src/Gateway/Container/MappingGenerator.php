<?php

declare(strict_types=1);

namespace Jasny\DB\Gateway\Container;

use Jasny\DB\Gateway\Container\IdGeneratorInterface;
use Jasny\DB\Gateway\Container\MappedIdGenerator;
use Jasny\DB\GatewayInterface;
use UnexpectedValueException;

/**
 * Generate a mapped idGenerator
 */
class MappingGenerator
{
    /**
     * @var IdGeneratorInterface
     */
    protected $idGenerator;

    /**
     * @var string
     */
    protected $class;

    /**
     * @var array
     */
    protected $mapping = [];


    /**
     * MappingGenerator constructor.
     *
     * @param IdGeneratorInterface $idGenerator
     * @param string               $class        Fully qualified class name of MappedIdGenerator class
     */
    public function __construct(IdGeneratorInterface $idGenerator, string $class = MappedIdGenerator::class)
    {
        if (!is_a($class, MappedIdGenerator::class, true)) {
            throw new UnexpectedValueException("$class is not a child class of " . MappedIdGenerator::class);
        }

        $this->idGenerator = $idGenerator;
        $this->class = $class;
    }

    /**
     * Iterate through files to find Gateways classes and generate the id
     *
     * @param iterable $classes
     * @return $this
     */
    public function append(iterable $classes): self
    {
        foreach ($classes as $class) {
            if (!is_a($class, GatewayInterface::class, true)) {
                continue;
            }

            $id = $this->idGenerator->fromGatewayClassToId($class);
            $this->mapping[$id] = $class;
        }

        return $this;
    }

    /**
     * Create the PHP script
     *
     * @return string
     */
    protected function createScript(): string
    {
        $map = var_export($this->mapping, true);

        return <<<PHP
<?php
return new {$this->class}($map);
PHP;
    }

    /**
     * Save as PHP script
     *
     * @param string $file  Path of the new script
     * @return void
     */
    public function save(string $file): void
    {
        file_put_contents($file, $this->createScript());
    }
}
