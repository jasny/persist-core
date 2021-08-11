<?php

declare(strict_types=1);

namespace Jasny\Persist\Option;

use Jasny\Persist\Filter\FilterItem;
use Jasny\Immutable;
use Jasny\Persist\Schema\Relationship;
use Jasny\Persist\Schema\SchemaInterface;

/**
 * Option to lookup related data from other collections / tables.
 */
class LookupOption implements OptionInterface
{
    use Immutable\With;

    protected string $name;
    protected ?string $target = null;
    protected string $related;

    protected int $relationshipType = -1;

    /** @var array<string,string> */
    protected array $match;

    protected bool $isCount = false;

    /** @var array<string,string>|FilterItem[] */
    protected array $filter = [];

    /** @var OptionInterface[] */
    protected array $opts = [];


    /**
     * Class constructor.
     *
     * @param string $related  Related collection name (or alias)
     */
    public function __construct(string $related)
    {
        $this->name = str_replace(':', '_', $related);
        $this->related = $related;
    }

    /**
     * Get a copy with a different field name.
     */
    public function as(string $name): static
    {
        return $this->withProperty('name', $name);
    }

    /**
     * Specify the field that the lookup applies to.
     * Null for the main collection of the query.
     */
    public function for(?string $field): static
    {
        return $this->withProperty('target', $field);
    }

    /**
     * Specify the relationship between the collections.
     *
     * @param int                  $type   One of the relationship constants
     * @param array<string,string> $match  Field pairs as ON in a JOIN statement
     * @return static
     */
    public function on(int $type, array $match): static
    {
        if ($type < 0 || $type > 0b11) {
            throw new \InvalidArgumentException("Invalid relationship type '$type'; use one of the constants");
        }

        return $this
            ->withProperty('relationshipType', $type)
            ->withProperty('match', $match);
    }

    /**
     * Filter the items from the related collection.
     *
     * @param array<string,string>|FilterItem[] $filter
     * @return static
     */
    public function having(array $filter): static
    {
        return $this->withProperty('filter', array_merge($this->filter, $filter));
    }

    /**
     * Only lookup a count of the number of items.
     */
    public function count(): static
    {
        return $this->withProperty('isCount', true);
    }

    /**
     * Specify which fields to include in the related data.
     */
    public function fields(string ...$fields): static
    {
        return $this->withPropertyItem('opts', new FieldsOption($fields));
    }

    /**
     * Specify which field to exclude from the related data.
     */
    public function omit(string ...$fields): static
    {
        return $this->withPropertyItem('opts', new FieldsOption($fields, true /* negate */));
    }

    /**
     * Specify which field to exclude from the related data.
     */
    public function sort(string ...$fields): static
    {
        return $this->withPropertyItem('opts', new SortOption($fields));
    }

    /**
     * Specify which field to exclude from hydrated data.
     */
    public function limit(int $limit, int $offset = 0): static
    {
        return $this->withPropertyItem('opts', new LimitOption($limit, $offset));
    }

    /**
     * Add custom option(s).
     */
    public function with(OptionInterface ...$opts): static
    {
        return $this->withProperty('opts', array_merge($this->opts, $opts));
    }


    /**
     * Get the field this lookup applies to.
     * Null for the main collection of the query.
     */
    public function getTarget(): ?string
    {
        return $this->target;
    }

    /**
     * Get related collection name (or alias).
     */
    public function getRelated(): string
    {
        return $this->related;
    }

    /**
     * Get the relationship, if the defined for the option.
     * If not defined, this method will return null and the schema should be used.
     */
    public function getRelationship(string $collection): ?Relationship
    {
        return $this->relationshipType >= 0
            ? new Relationship($this->relationshipType, $collection, $this->related, $this->match)
            : null;
    }

    /**
     * Get field name.
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * Should a count of the number of items be looked up?
     */
    public function isCount(): bool
    {
        return $this->isCount;
    }

    /**
     * Get filter for related items.
     *
     * @return array<string,string>|FilterItem[]
     */
    public function getFilter(): array
    {
        return $this->filter;
    }

    /**
     * Get options specific to the lookup.
     *
     * @return OptionInterface[]
     */
    public function getOpts(): array
    {
        return $this->opts;
    }
}
