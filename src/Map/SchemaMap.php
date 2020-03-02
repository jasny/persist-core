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
    use Traits\ProxyTrait;

    protected string $collection;
    protected SchemaInterface $schema;

    /**
     * Class constructor.
     */
    public function __construct(string $collection, SchemaInterface $schema)
    {
        $this->collection = $collection;
        $this->schema = $schema;

        $this->inner = $schema->getMap($collection);
    }

    /**
     * Add a map for a relationship.
     * If field maps to a single relationship, the related collection and fields don't have to be specified.
     *
     * @param string               $field         Collection field name or alias
     * @param string|null          $related       Related collection name
     * @param string|string[]|null $relatedField  Fields of related collection
     * @return static
     */
    public function withRelated(string $field, ?string $related, $relatedField): self
    {
        $field = $related !== null ? $field : null;
        $relationship = $this->schema->getRelationship($this->collection, $field, $related, $relatedField);
        $relatedMap = $this->schema->map($related);
        $relatedField .= ($relationship->isFromMany() ? '[]' : '');

        $map = $this->inner instanceof NestedMap ? $this->inner : new NestedMap($this->inner);

        return $this->withProperty('inner', $map->withMappedField($relatedField, $relatedMap));
    }

    /**
     * Use `lookup` and `expand` query options on map.
     *
     * @param OptionInterface[] $opts
     * @return static
     */
    public function withOpts(array $opts): self
    {
        $map = $this;

        /** @var LookupOption $lookup */
        foreach (i\iterable_filter($opts, fn($opt) => $opt instanceof LookupOption) as $lookup) {
            $map = $map->withRelated($lookup->getField(), ...$lookup->getRelated());
        }

        return $map;
    }
}
