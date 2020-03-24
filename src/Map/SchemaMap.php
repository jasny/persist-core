<?php

declare(strict_types=1);

namespace Jasny\DB\Map;

use Improved as i;
use Jasny\DB\Option\LookupOption;
use Jasny\DB\Option\OptionInterface;
use Jasny\DB\Schema\SchemaInterface;
use Jasny\Immutable;

/**
 * A field map based on a database schema.
 * @immutable
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
     * Add a map for a relationship on the given field.
     *
     * @param string $field
     * @return static
     */
    public function withHydrated(string $field): self
    {
        $relationship = $this->schema->getRelationship($this->collection, $field);
        $field .= $relationship->isFromMany() ? '[]' : '';

        return $this->withNested($field, $relationship->getRelatedCollection());
    }

    /**
     * Add a map for a relationship.
     *
     * @param string      $field
     * @param string      $collection    Related collection name
     * @param string|null $relatedField  Field of related collection
     * @return static
     */
    public function withRelated(string $field, string $collection, ?string $relatedField = null): self
    {
        $relationship = $this->schema->getRelationship($this->collection, null, $collection, $relatedField);
        $field .= $relationship->isFromMany() ? '[]' : '';

        return $this->withNested($field, $relationship->getRelatedCollection());
    }

    /**
     * Add a map for a nested field (without a predefined relationship).
     *
     * @param string $field       Field name; suffix with `[]` if field will contain many items.
     * @param string $collection  Related collection name
     * @return static
     */
    public function withNested(string $field, string $collection): self
    {
        $relatedMap = $this->schema->getMapOf($collection);

        $map = $this->inner instanceof NestedMap ? $this->inner : new NestedMap($this->inner);

        return $this->withProperty('inner', $map->withMappedField($field, $relatedMap));
    }


    /**
     * Use `lookup` and `expand` query options on map.
     *
     * @param OptionInterface[] $opts
     * @return static
     */
    public function withOpts(array $opts): self
    {
        $map = $this->withInnerOpts($opts);

        foreach (i\iterable_filter($opts, fn($opt) => $opt instanceof LookupOption) as $lookup) {
            /** @var LookupOption $lookup */
            $map = $lookup->getRelatedCollection() === null
                ? $map->withHydrated($lookup->getField())
                : $map->withRelated($lookup->getField(), $lookup->getRelatedCollection(), $lookup->getRelatedField());
        }

        return $map;
    }
}
