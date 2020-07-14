<?php

declare(strict_types=1);

namespace Jasny\DB\Map;

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
     * @param Relationship $relationship  Related collection name
     * @return static
     */
    protected function withNested(string $name, Relationship $relationship): self
    {
        $relatedMap = $this->schema->getMapOf($relationship->getRelatedCollection());
        $map = $this->inner instanceof NestedMap ? $this->inner : new NestedMap($this->inner);

        $name .= $relationship->isToMany() ? '[]' : '';

        return $this->withProperty('inner', $map->withMappedField($name, $relatedMap));
    }


    /**
     * Use `lookup` and `expand` query options on map.
     *
     * @param OptionInterface[] $opts
     * @return static
     */
    public function withOpts(array $opts): self
    {
        /** @var static $map */
        $map = $this->withInnerOpts($opts);

        foreach ($opts as $opt) {
            if ($opt instanceof HydrateOption) {
                $relationship = $this->schema->getRelationshipForField($this->collection, $opt->getField());
            } elseif ($opt instanceof LookupOption) {
                $relationship = $this->schema->getRelationship($this->collection, $opt->getRelated());
            } else {
                continue;
            }

            $map = $map->withNested($opt->getName(), $relationship);
        }

        return $map;
    }
}
