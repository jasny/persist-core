<?php

declare(strict_types=1);

namespace Jasny\Persist\Map;

/**
 * Null object for map.
 */
final class NoMap implements MapInterface
{
    /**
     * @inheritDoc
     */
    public function withOpts(array $opts): self
    {
        return $this;
    }

    /**
     * @inheritDoc
     */
    public function applyToField(string $field): string|null|false
    {
        return null;
    }

    /**
     * @inheritDoc
     */
    public function applyInverse(array|object $item): array|object
    {
        return $item;
    }

    /**
     * @inheritDoc
     */
    public function apply(array|object $item): array|object
    {
        return $item;
    }
}
