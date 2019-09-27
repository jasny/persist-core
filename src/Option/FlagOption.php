<?php

declare(strict_types=1);

namespace Jasny\DB\Option;

use Improved as i;

/**
 * Generic flag for the query builder.
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

    /**
     * See if this flag exists in a set of options.
     *
     * @param OptionInterface[] $opts
     * @return bool
     */
    public function isInSet(array $opts): bool
    {
        return i\iterable_has_any($opts, (fn($opt) => $opt instanceof self && $opt->getType() === $this->type));
    }
}
