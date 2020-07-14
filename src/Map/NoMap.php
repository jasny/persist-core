<?php

declare(strict_types=1);

namespace Jasny\DB\Map;

use Jasny\DB\Option\OptionInterface;

/**
 * Null object for map.
 */
final class NoMap implements MapInterface
{
    /**
     * @inheritDoc
     */
    public function withOpts(array $opts): MapInterface
    {
        return $this;
    }

    /**
     * @inheritDoc
     */
    public function applyToField(string $field)
    {
        return null;
    }

    /**
     * @inheritDoc
     */
    public function applyInverse($item)
    {
        return $item;
    }

    /**
     * @inheritDoc
     */
    public function apply($item)
    {
        return $item;
    }
}
