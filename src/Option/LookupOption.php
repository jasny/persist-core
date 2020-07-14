<?php

declare(strict_types=1);

namespace Jasny\DB\Option;

use Jasny\DB\Filter\FilterItem;
use Jasny\Immutable;

/**
 * Option to lookup related data from other collections / tables.
 */
class LookupOption implements OptionInterface
{
    use Immutable\With;

    protected string $name;
    protected string $related;

    /** @var array<string,string>|FilterItem[] */
    protected array $filter = [];

    /**
     * Class constructor
     * If field maps to a single relationship, the related collection and fields don't have to be specified.
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
     *
     * @return static
     */
    public function as(string $name): self
    {
        return $this->withProperty('name', $name);
    }

    /**
     * Filter the items from the related collection.
     *
     * @param array<string,string>|FilterItem[]
     * @return static
     */
    public function where(array $filter): self
    {
        return $this->withProperty('filter', array_merge($this->filter, $filter));
    }


    /**
     * Get related collection name (or alias).
     */
    public function getRelated(): string
    {
        return $this->related;
    }

    /**
     * Get field name.
     */
    public function getName(): string
    {
        return $this->name;
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
}
