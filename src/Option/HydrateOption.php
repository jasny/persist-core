<?php

declare(strict_types=1);

namespace Jasny\DB\Option;

use Jasny\Immutable;

/**
 * Option to hydrate a field by looking up related data from other collections / tables.
 */
class HydrateOption implements OptionInterface
{
    use Immutable\With;

    protected string $field;
    protected string $name;

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
}
