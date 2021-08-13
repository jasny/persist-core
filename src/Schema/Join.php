<?php

declare(strict_types=1);

namespace Jasny\Persist\Schema;

use Jasny\Immutable;
use function Jasny\str_before;

/**
 * Field pairs as ON in JOIN statement.
 *
 * new Join(['a' => 'b', 'q' => 'r']) means JOIN ON a = b AND q = r
 */
final class Join implements JoinInterface
{
    /** @var array<string,string> */
    protected array $match;

    /**
     * @param array<string,string> $match
     */
    public function __construct(array $match)
    {
        $this->match = $match;
    }

    /**
     * @return array<string,string>
     */
    public function getMatch(): array
    {
        return $this->match;
    }

    /**
     * @inheritDoc
     */
    public function swapped(): static
    {
        return new self(array_flip($this->match));
    }

    /**
     * @inheritDoc
     */
    public function isOnField(string ...$fields): bool
    {
        return count($fields) === count($this->match) && array_diff($fields, array_keys($this->match)) === [];
    }
}
