<?php

declare(strict_types=1);

namespace Jasny\DB\Option;

/**
 * Sort on specified fields.
 * Prepend field with `~` for descending order.
 */
class SortOption implements OptionInterface
{
    /**
     * @var string[]
     */
    protected array $fields;

    /**
     * Class constructor.
     *
     * @param string[] $fields
     */
    public function __construct(array $fields)
    {
        $this->fields = $fields;
    }

    /**
     * Get the fields
     *
     * @return string[]
     */
    public function getFields(): array
    {
        return $this->fields;
    }
}
