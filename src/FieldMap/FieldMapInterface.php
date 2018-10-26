<?php declare(strict_types=1);

namespace Jasny\DB\FieldMap;

/**
 * Map MongoDB field to PHP field or visa versa
 */
interface FieldMapInterface extends \ArrayAccess
{
    /**
     * Allow properties that are not mapped?
     *
     * @return bool
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
     * @param iterable $iterable
     * @return iterable
     */
    public function __invoke(iterable $iterable): iterable;
}
