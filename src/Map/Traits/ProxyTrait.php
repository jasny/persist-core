<?php

declare(strict_types=1);

namespace Jasny\Persist\Map\Traits;

use Jasny\Persist\Map\MapInterface;
use Jasny\Persist\Option\OptionInterface;

/**
 * Trait for maps that simply proxies to another map.
 */
trait ProxyTrait
{
    protected MapInterface $inner;

    /**
     * Apply options to map.
     *
     * @param OptionInterface[] $opts
     * @return static&MapInterface
     */
    public function withOpts(array $opts): static
    {
        $inner = $this->inner->withOpts($opts);

        if ($inner === $this->inner) {
            return $this;
        }

        $copy = clone $this;
        $copy->inner = $inner;

        return $copy;
    }

    /**
     * Get wrapped map.
     */
    public function getInner(): MapInterface
    {
        return $this->inner;
    }

    /**
     * Map App field to DB field.
     * Returns null if field isn't mapped and false if field is omitted.
     */
    public function applyToField(string $field): string|false|null
    {
        return $this->inner->applyToField($field);
    }

    /**
     * Get function to apply mapping to items, so the data can be used by the DB.
     *
     * @template TItem
     * @phpstan-param TItem&(array<string,mixed>|object) $item
     * @phpstan-return TItem&(array<string,mixed>|object)
     */
    public function apply(array|object $item): array|object
    {
        return $this->inner->apply($item);
    }

    /**
     * Get function to apply mapping to items of query result.
     *
     * @template TItem
     * @phpstan-param TItem&(array<string,mixed>|object) $item
     * @phpstan-return TItem&(array<string,mixed>|object)
     */
    public function applyInverse(array|object $item): array|object
    {
        return $this->inner->applyInverse($item);
    }
}
