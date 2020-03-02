<?php

declare(strict_types=1);

namespace Jasny\DB\Option;

use Jasny\Immutable;

/**
 * Option to lookup related data from other collections / tables.
 */
class LookupOption
{
    use Immutable\With;

    protected string $field;
    protected ?string $related;

    /** @var string[]|null */
    protected ?array $relatedField;

    /**
     * Class constructor
     * If field maps to a single relationship, the related collection and fields don't have to be specified.
     *
     * @param string               $field         Collection field name or alias
     * @param string|null          $related       Related collection name
     * @param string|string[]|null $relatedField  Fields of related collection
     */
    public function __construct(string $field, ?string $related = null, $relatedField = null)
    {
        $this->field = $field;
        $this->related = $related;
        $this->relatedField = is_string($relatedField) ? [$relatedField] : $relatedField;
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
     * @return string|string[]|null
     */
    public function getRelatedField()
    {
        return $this->relatedField;
    }
}
