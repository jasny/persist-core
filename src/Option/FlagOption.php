<?php

declare(strict_types=1);

namespace Jasny\DB\Option;

/**
 * Generic option.
 * @immutable
 */
class FlagOption
{
    protected string $type;

    /**
     * FlagOption constructor.
     */
    public function __construct(string $type)
    {
        $this->type = $type;
    }

    /**
     * Get the flag type.
     */
    public function getType(): string
    {
        return $this->type;
    }
}
