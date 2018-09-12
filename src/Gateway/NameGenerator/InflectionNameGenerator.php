<?php

declare(strict_types=1);

namespace Jasny\DB\Gateway\NameGenerator;

use Doctrine\Inflector\Inflector;
use Doctrine\Inflector\CachedWordInflector;
use Doctrine\Inflector\RulesetInflector;
use Doctrine\Inflector\Rules\English;

use function Jasny\str_ends_with;

/**
 * Convert generator class to name and visa versa using inflection
 */
class InflectionNameGenerator implements NameGeneratorInterface
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
     * InflectionNameGenerator constructor.
     *
     * @param string    $ns         Namespace
     * @param Inflector $inflector
     */
    public function __construct(string $ns, Inflector $inflector = null)
    {
        $this->ns = $ns;
        $this->inflector = $inflector ?? self::createDefaultInflector();
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
     * Turn a container name to a Entity class
     *
     * @param string $name
     * @return string
     */
    public function fromNameToEntityClass(string $name): string
    {
        $word = $this->inflector->singularize($name);
        $entityClass = $this->inflector->classify($word);

        return ($this->ns === '' ? '' : $this->ns . '\\') . $entityClass;
    }

    /**
     * Turn a container name to a Gateway class
     *
     * @param string $name
     * @return string
     */
    public function fromNameToGatewayClass(string $name): string
    {
        $entityClass = $this->fromNameToEntityClass($name);

        return $this->fromEntityClassToGatewayClass($entityClass);
    }


    /**
     * Get a class name from the fully qualified class name
     *
     * @param string $fqcn
     * @return string
     * @throws \UnexpectedValueException
     */
    protected function getClassname(string $fqcn): string
    {
        $parts = explode('\\', $fqcn);
        $ns = join('\\', array_slice($parts, 0, -1));
        $classname = end($parts);

        if ($ns !== $this->ns) {
            throw new \UnexpectedValueException("Class '$fqcn' is not in "
                . ($this->ns === '' ? "root namespace" : "expected namespace '$this->ns'"));
        }

        return $classname;
    }

    /**
     * Turn a class name to an name
     *
     * @param string $class
     * @return string
     */
    protected function fromClassnameToName(string $class): string
    {
        $word = $this->inflector->tableize($class);
        $name = $this->inflector->pluralize($word);

        return $name;
    }

    /**
     * Turn an Entity class to a container name
     *
     * @param string $fqcn
     * @return string
     */
    public function fromEntityClassToName(string $fqcn): string
    {
        $class = $this->getClassname($fqcn);

        return $this->fromClassnameToName($class);
    }

    /**
     * Turn an Entity class to a container name
     *
     * @param string $fqcn
     * @return string
     */
    public function fromEntityClassToGatewayClass(string $fqcn): string
    {
        return $fqcn . 'Gateway';
    }


    /**
     * Turn a Gateway class to a container name
     *
     * @param string $fqcn
     * @return string
     */
    public function fromGatewayClassToName(string $fqcn): string
    {
        $class = $this->getClassname($fqcn);
        $entityClass = $this->fromGatewayClassToEntityClass($class);

        return $this->fromClassnameToName($entityClass);
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
            throw new \UnexpectedValueException("Invalname gateway class name '$fqcn': should end in 'Gateway'");
        }

        return substr($fqcn, 0, -7);
    }


    /**
     * Create the default inflector for English class names.
     *
     * @return Inflector
     */
    final public static function createDefaultInflector(): Inflector
    {
        $singular = new RulesetInflector(English\Rules::getSingularRuleset());
        $plural = new RulesetInflector(English\Rules::getPluralRuleset());

        return new Inflector(new CachedWordInflector($singular), new CachedWordInflector($plural));
    }
}
