<?php

declare(strict_types=1);

namespace Jasny\Persist\Map;

use Jasny\DotKey\DotKey;

/**
 * Simple field map.
 */
final class FieldMap implements MapInterface
{
    /** @var array<string,string|false> */
    protected array $map;
    /** @var array<string,string> */
    protected array $inverse;

    /**
     * Class constructor.
     *
     * @param array<string,string|false> $map
     */
    public function __construct(array $map)
    {
        $this->map = $map;
        $this->inverse = array_flip(array_filter($map));
    }

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
    public function applyToField(string $field): string|false|null
    {
        [$top, $rest] = explode('.', $field, 2) + [1 => null];

        if (!isset($this->map[$top])) {
            return null;
        }

        return $this->map[$top] === false || $rest === null
            ? $this->map[$top]
            : $this->map[$top] . '.' . $rest;
    }

    /**
     * @inheritDoc
     */
    public function apply(array|object $item): array|object
    {
        return $this->applyMap($this->map, $item);
    }

    /**
     * @inheritDoc
     */
    public function applyInverse(array|object $item): array|object
    {
        return $this->applyMap($this->inverse, $item);
    }

    /**
     * Apply mapping to item.
     *
     * @template TItem
     * @phpstan-param array<string,string|false>         $map
     * @phpstan-param TItem&(array<string,mixed>|object) $item
     * @phpstan-return TItem&(array<string,mixed>|object)
     */
    protected function applyMap(array $map, array|object $item): array|object
    {
        $set = [];
        $remove = [];

        foreach ($map as $field => $newField) {
            if (!DotKey::on($item)->exists($field)) {
                continue;
            }

            if ($newField !== false) {
                $set[$newField] = DotKey::on($item)->get($field);
            }
            $remove[] = $field;
        }

        $copy = $item;
        $dotkey = new DotKey($copy, DotKey::COPY);

        foreach ($remove as $field) {
            $dotkey->remove($field);
        }
        foreach ($set as $field => $value) {
            $dotkey->put($field, $value);
        }

        return $copy;
    }
}
