<?php

declare(strict_types=1);

namespace Jasny\DB\FieldMap;

/**
 * Map DB field to PHP field or visa versa. Also works for parsed filters.
 * For results, the result builder should map each item to the field map.
 */
interface FieldMapInterface extends \ArrayAccess
{
    /**
     * Allow properties that are not mapped?
     */
    public function isDynamic(): bool;

    /**
     * Get the inverse of the map
     *
     * @return static
     */
    public function flip();

    /**
     * Apply mapping.
     *
     * @param mixed $subject
     * @return mixed
     */
    public function __invoke($subject);
}
