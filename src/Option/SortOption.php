<?php

declare(strict_types=1);

namespace Jasny\DB\Option;

use Improved as i;

/**
 * Sort on specified fields.
 * Prepend field with `~` for descending order.
 */
class SortOption implements QueryOptionInterface
{
    /**
     * @var string[]
     */
    protected $fields;

    /**
     * Class constructor.
     *
     * @param string ...$fields
     */
    public function __construct(string ...$fields)
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
