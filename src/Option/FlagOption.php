<?php

declare(strict_types=1);

namespace Jasny\DB\Option;

use Improved as i;

/**
 * Generic flag for the query builder.
 *
 * Example for checking if the 'fast' flag is set.
 *
 *     $isFast = opt\flag('fast')->isIn($opts);
 *
 */
class FlagOption implements OptionInterface
{
    protected string $name;

    /**
     * FlagOption constructor.
     */
    public function __construct(string $name)
    {
        $this->name = $name;
    }

    /**
     * Get the flag name.
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * See if this flag exists in a set of options.
     *
     * @param OptionInterface[] $opts
     * @return bool
     */
    public function isIn(array $opts): bool
    {
        return i\iterable_has_any($opts, (fn($opt) => $opt instanceof self && $opt->getName() === $this->name));
    }
}
