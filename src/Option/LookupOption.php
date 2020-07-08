<?php

declare(strict_types=1);

namespace Jasny\DB\Option;

use Jasny\Immutable;

/**
 * Option to lookup related data from other collections / tables.
 */
class LookupOption implements OptionInterface
{
    use Immutable\With;

    protected string $field;
    protected ?string $related;

    /** @var array<string,string>|null */
    protected ?array $match;

    /**
     * Class constructor
     * If field maps to a single relationship, the related collection and fields don't have to be specified.
     *
     * @param string                    $field    Collection field name or alias
     * @param string|null               $related  Related collection name
     * @param array<string,string>|null $match    Fields of related collection
     */
    public function __construct(string $field, ?string $related = null, ?array $match = null)
    {
        $this->field = $field;
        $this->related = $related;
        $this->match = $match;
    }

    /**
     * Get a copy with a different field name.
     *
     * @return static
     */
    public function as(string $field): self
    {
        if ($this->related === null) {
            throw new \LogicException("Unable to change field name when expanding specific field");
        }

        return $this->withProperty('field', $field);
    }

    /**
     * Get local field name.
     */
    public function getField(): string
    {
        return $this->field;
    }

    /**
     * Get related collection.
     */
    public function getRelatedCollection(): ?string
    {
        return $this->related;
    }

    /**
     * Get related field.
     *
     * @return array<string,string>|null
     */
    public function getMatch(): ?array
    {
        return $this->match;
    }
}
