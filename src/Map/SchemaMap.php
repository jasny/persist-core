<?php

declare(strict_types=1);

namespace Jasny\DB\Map;

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
     * MappedSchema constructor.
     */
    public function __construct(string $collection, SchemaInterface $schema)
    {
        $this->collection = $collection;
        $this->schema = $schema;

        $this->inner = $schema->getFieldMap($collection);
    }

    /**
     * Map a relationship.
     * If field maps to a single relationship, the related collection and fields don't have to be specified.
     *
     * @param string               $field         Collection field name or alias
     * @param string|null          $related       Related collection name
     * @param string|string[]|null $relatedField  Fields of related collection
     * @return static
     */
    public function withRelated(string $field, ?string $related, $relatedField): self
    {
        $relationship = $this->schema->getRelationship(
            $this->collection,
            $related !== null ? $field : null,
            $related,
            $relatedField
        );
        $relatedMap = $this->schema->map($related);
        $relatedField .= ($relationship->isFromMany() ? '[]' : '');

        $map = $this->inner instanceof NestedMap ? $this->inner : new NestedMap($this->inner);

        return $this->withProperty('inner', $map->withMappedField($relatedField, $relatedMap));
    }
}
