<?php

declare(strict_types=1);

namespace Jasny\Persist\Schema;

/**
 * Relationship join.
 */
interface JoinInterface
{
    /**
     * Swap left and right sides.
     */
    public function swapped(): static;

    /**
     * Check if the join is on the field(s) on the left side.
     */
    public function isOnField(string ...$fields): bool;
}
