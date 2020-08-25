<?php

declare(strict_types=1);

namespace Jasny\DB\Map;

use Jasny\DB\Exception\LookupException;
use Jasny\DB\Option\HydrateOption;
use Jasny\DB\Option\LookupOption;
use Jasny\DB\Option\OptionInterface;
use Jasny\DB\Schema\Relationship;
use Jasny\DB\Schema\SchemaInterface;
use Jasny\Immutable;

/**
 * A field map based on a database schema.
 */
final class SchemaMap implements MapInterface
{
    use Immutable\With;
    use Traits\ProxyTrait {
        withOpts as protected withInnerOpts;
    }

    protected string $collection;
    protected SchemaInterface $schema;

    /**
     * Class constructor.
     */
    public function __construct(string $collection, SchemaInterface $schema)
    {
        $this->collection = $collection;
        $this->schema = $schema;

        $this->inner = $schema->getMapOf($collection);
    }

    /**
     * Add a map for a nested field (without a predefined relationship).
     *
     * @param string       $name          Field name
     * @param string       $target
     * @param Relationship $relationship  Related collection name
     * @return static
     */
    protected function withNested(string $name, string $target, Relationship $relationship): self
    {
        $relatedMap = $this->schema->getMapOf($relationship->getRelatedCollection());

        // Quick return if there's no map for the nested lookup
        if ($relatedMap instanceof NoMap) {
            return $this;
        }

        $levels = $target !== '' ? explode('.', $target) : [];

        $map = $this->traverseNested(
            $this->inner,
            $levels,
            fn(NestedMap $map) => $map->withMappedField(
                $name . ($relationship->isToMany() ? '[]' : ''),
                $relatedMap
            )
        );

        return $this->withProperty('inner', $map);
    }

    /**
     * @param MapInterface $map
     * @param array $levels
     * @param callable $callback
     * @return MapInterface
     */
    private function traverseNested(MapInterface $map, array $levels, callable $callback): MapInterface
    {
        if (!$map instanceof NestedMap) {
            $map = new NestedMap($map);
        }

        if ($levels === []) {
            return $callback($map);
        }

        $level = array_shift($levels);

        /** @var ChildMap|null $childMap */
        $childMap = $map->getInner()[$level] ?? null;
        $subMap = $childMap !== null ? $childMap->getInner() : new NoMap();

        $newSubMap = $this->traverseNested($subMap, $levels, $callback);

        return $map->withMappedField(
            $level . ($childMap !== null && $childMap->isForMany() ? '[]' : ''),
            $newSubMap
        );
    }


    /**
     * Apply `lookup` and `hydrate` query options to the map.
     *
     * @param OptionInterface[] $opts
     * @return static
     */
    public function withOpts(array $opts): self
    {
        return $this
            ->withInnerOpts($opts)
            ->applyOpts(null, ['' => $this->collection], $opts);
    }

    /**
     * Recursively apply `lookup` and `hydrate` query options to the map.
     *
     * @param string|null          $baseTarget
     * @param array<string,string> $cols        Field to collection mapping
     * @param OptionInterface[]    $opts
     * @return static
     */
    protected function applyOpts(?string $baseTarget, array $cols, array $opts): self
    {
        $map = $this;

        foreach ($opts as $opt) {
            if (!$opt instanceof HydrateOption && !$opt instanceof LookupOption) {
                continue;
            }

            $optTarget = $opt->getTarget();
            $target = $baseTarget !== null && $optTarget !== null
                ? "{$baseTarget}.{$optTarget}"
                : $baseTarget ?? (string)$optTarget;

            if (!isset($cols[$target])) {
                throw $this->lookupException($opt, $target);
            }

            $relationship = $opt instanceof HydrateOption
                ? $this->schema->getRelationshipForField($cols[$target], $opt->getField())
                : $this->schema->getRelationship($cols[$target], $opt->getRelated());

            $fullName = ($target !== '' ? $target . '.' : '') . $opt->getName();
            $cols[$fullName] = $relationship->getRelatedCollection();

            $map = $map
                ->withNested($opt->getName(), $target, $relationship)
                ->applyOpts($fullName, $cols, $opt->getOpts());
        }

        return $map;
    }

    /**
     * @param LookupOption|HydrateOption $opt
     * @param string                     $target
     * @return LookupException
     */
    private function lookupException($opt, string $target): LookupException
    {
        $type = $opt instanceof HydrateOption ? "hydrate" : "lookup";
        $rel = $opt instanceof HydrateOption ? $opt->getField() : $opt->getRelated();
        $as = ($rel !== $opt->getName() ? " as " . $opt->getName() : '');

        return new LookupException("Unable to $type '$rel'$as; no lookup or hydrate for field '$target'");
    }
}
