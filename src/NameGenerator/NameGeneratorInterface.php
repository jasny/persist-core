<?php

declare(strict_types=1);

namespace Jasny\DB\NameGenerator;

use Doctrine\Inflector\Inflector;
use Doctrine\Inflector\CachedWordInflector;
use Doctrine\Inflector\RulesetInflector;
use Doctrine\Inflector\Rules\English;

use function Jasny\str_ends_with;

/**
 * Convert Gateway or Entity class to container name and visa versa using inflection
 */
interface NameGeneratorInterface
{
    /**
     * Turn a container name to a Entity class
     *
     * @param string $name
     * @return string
     */
    public function fromNameToEntityClass(string $name): string;

    /**
     * Turn a container name to a Gateway class
     *
     * @param string $name
     * @return string
     */
    public function fromNameToGatewayClass(string $name): string;


    /**
     * Turn an Entity class to a container name
     *
     * @param string $fqcn
     * @return string
     */
    public function fromEntityClassToName(string $fqcn): string;

    /**
     * Turn an Entity class to a container name
     *
     * @param string $fqcn
     * @return string
     */
    public function fromEntityClassToGatewayClass(string $fqcn): string;


    /**
     * Turn a Gateway class to a container name
     *
     * @param string $fqcn
     * @return string
     */
    public function fromGatewayClassToName(string $fqcn): string;

    /**
     * Turn a Gateway class to an Entity class
     *
     * @param string $fqcn
     * @return string
     */
    public function fromGatewayClassToEntityClass(string $fqcn): string;
}
