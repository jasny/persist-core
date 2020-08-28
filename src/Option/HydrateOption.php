<?php

declare(strict_types=1);

namespace Jasny\Persist\Option;

use Jasny\Immutable;

/**
 * Option to hydrate a field by looking up related data from other collections / tables.
 */
class HydrateOption implements OptionInterface
{
    use Immutable\With;

    protected string $field;
    protected string $name;
    protected ?string $target = null;

    /** @var OptionInterface[] */
    protected array $opts = [];

    /**
     * Class constructor.
     */
    public function __construct(string $field)
    {
        $this->field = $field;
        $this->name = (string)preg_replace('/(?<=[a-z0-9])(?:_id|Id|ID)$/', '', $field);
    }

    /**
     * Use an alternative name in the result.
     *
     * @return static
     */
    public function as(string $name): self
    {
        return $this->withProperty('name', $name);
    }

    /**
     * Specify the field that the lookup applies to.
     * Null for the main collection of the query.
     *
     * @param string|null $field
     * @return static
     */
    public function for(?string $field): self
    {
        return $this->withProperty('target', $field);
    }

    /**
     * Specify which fields to include in the hydrated data.
     *
     * @param string ...$fields
     * @return static
     */
    public function fields(string ...$fields): self
    {
        return $this->withPropertyItem('opts', new FieldsOption($fields));
    }

    /**
     * Specify which field to exclude from the hydrated data.
     *
     * @param string ...$fields
     * @return static
     */
    public function omit(string ...$fields): self
    {
        return $this->withPropertyItem('opts', new FieldsOption($fields, true /* negate */));
    }

    /**
     * Add custom option(s).
     *
     * @param OptionInterface ...$opts
     * @return static
     */
    public function with(OptionInterface ...$opts): self
    {
        return $this->withProperty('opts', array_merge($this->opts, $opts));
    }


    /**
     * Get the field this lookup applies to.
     * Null for the main collection of the query.
     *
     * @return string|null
     */
    public function getTarget(): ?string
    {
        return $this->target;
    }

    /**
     * Get the field name that should be hydrated.
     */
    public function getField(): string
    {
        return $this->field;
    }

    /**
     * The the field name for the result.
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * Get options specific to hydration.
     *
     * @return OptionInterface[]
     */
    public function getOpts(): array
    {
        return $this->opts;
    }
}
