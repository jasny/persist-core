<?php

declare(strict_types=1);

namespace Jasny\DB\Gateway\Container;

use Doctrine\Inflector\Inflector;
use Doctrine\Inflector\CachedWordInflector;
use Doctrine\Inflector\RulesetInflector;
use Doctrine\Inflector\Rules\English;
use UnexpectedValueException;

use function Jasny\str_ends_with;

/**
 * Conver generator class to id and visa versa using inflection
 */
class InflectionIdGenerator implements IdGeneratorInterface
{
    /**
     * @var string
     */
    protected $ns;

    /**
     * @var Inflector
     */
    protected $inflector;

    /**
     * InflectionIdGenerator constructor.
     *
     * @param string    $ns         Namespace
     * @param Inflector $inflector
     */
    public function __construct(string $ns, Inflector $inflector)
    {
        $this->ns = $ns;
        $this->inflector = $inflector;
    }

    /**
     * Get the namespace.
     *
     * @return string
     */
    public function getNamespace(): string
    {
        return $this->ns;
    }

    /**
     * Get the inflector.
     *
     * @return Inflector
     */
    public function getInflector(): Inflector
    {
        return $this->inflector;
    }


    /**
     * Turn a container id to a Entity class
     *
     * @param string $id
     * @return string
     */
    public function fromIdToEntityClass(string $id): string
    {
        $word = $this->inflector->singularize($id);
        $entityClass = $this->inflector->classify($word);

        return ($this->ns === '' ? '' : $this->ns . '\\') . $entityClass;
    }

    /**
     * Turn a container id to a Gateway class
     *
     * @param string $id
     * @return string
     */
    public function fromIdToGatewayClass(string $id): string
    {
        $entityClass = $this->fromIdToEntityClass($id);

        return $this->fromEntityClassToGatewayClass($entityClass);
    }


    /**
     * Get a class name from the fully qualified class name
     *
     * @param string $fqcn
     * @return string
     * @throws UnexpectedValueException
     */
    protected function getClassname(string $fqcn): string
    {
        $parts = explode('\\', $fqcn);
        $ns = join('\\', array_slice($parts, 0, -1));
        $classname = end($parts);

        if ($ns !== $this->ns) {
            throw new UnexpectedValueException("Class '$fqcn' is not in "
                . ($this->ns === '' ? "root namespace" : "expected namespace '$this->ns'"));
        }

        return $classname;
    }

    /**
     * Turn a class name to an id
     *
     * @param string $class
     * @return string
     */
    protected function fromClassnameToId(string $class): string
    {
        $word = $this->inflector->tableize($class);
        $id = $this->inflector->pluralize($word);

        return $id;
    }

    /**
     * Turn an Entity class to a container id
     *
     * @param string $fqcn
     * @return string
     */
    public function fromEntityClassToId(string $fqcn): string
    {
        $class = $this->getClassname($fqcn);

        return $this->fromClassnameToId($class);
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
        $class = $this->getClassname($fqcn);
        $entityClass = $this->fromGatewayClassToEntityClass($class);

        return $this->fromClassnameToId($entityClass);
    }

    /**
     * Turn a Gateway class to an Entity class
     *
     * @param string $fqcn
     * @return string
     */
    public function fromGatewayClassToEntityClass(string $fqcn): string
    {
        if (!str_ends_with($fqcn, 'Gateway')) {
            throw new UnexpectedValueException("Invalid gateway class name '$fqcn': should end in 'Gateway'");
        }

        return substr($fqcn, 0, -7);
    }


    /**
     * Create the default id generator for English class names.
     *
     * @param string $ns  Namespace
     * @return static
     */
    public static function createDefault($ns = ''): self
    {
        $singular = new RulesetInflector(English\Rules::getSingularRuleset());
        $plural = new RulesetInflector(English\Rules::getPluralRuleset());

        $inflector = new Inflector(new CachedWordInflector($singular), new CachedWordInflector($plural));

        return new static($ns, $inflector);
    }
}
