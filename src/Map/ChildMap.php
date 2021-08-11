<?php

declare(strict_types=1);

namespace Jasny\Persist\Map;

use Improved as i;
use Improved\IteratorPipeline\Pipeline;
use Jasny\DotKey\DotKey;
use function Jasny\str_starts_with;
use function Jasny\str_ends_with;

/**
 * Field map for a nested item.
 */
final class ChildMap implements MapInterface
{
    protected string $field;
    protected bool $isForMany;

    protected MapInterface $map;

    /**
     * ChildFieldMap constructor.
     *
     * @param string                                  $field
     * @param MapInterface|array<string,string|false> $map
     */
    public function __construct(string $field, $map)
    {
        $field = str_replace('/', '.', $field);

        $this->isForMany = str_ends_with($field, '[]');
        $this->field = $this->isForMany ? substr($field, 0, -2) : $field;

        $this->map = $map instanceof MapInterface ? $map : new FieldMap($map);
    }

    /**
     * Get the field to which the map applies.
     */
    public function getField(): string
    {
        return $this->field;
    }

    /**
     * Get the map for the nested item.
     */
    public function getInner(): MapInterface
    {
        return $this->map;
    }

    /**
     * Return true if the map is applied to an iterable.
     */
    public function isForMany(): bool
    {
        return $this->isForMany;
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
        if (!str_starts_with($field, $this->field . '.')) {
            return null;
        }

        $mappedField = $this->map->applyToField(substr($field, strlen($this->field) + 1));

        return is_string($mappedField) ? $this->field . '.' . $mappedField : $mappedField;
    }

    /**
     * @inheritDoc
     */
    public function apply(array|object $item): array|object
    {
        return $this->applyToItem(
            $item,
            fn($value) => is_array($value) || is_object($value) ? $this->map->apply($value) : $value
        );
    }

    /**
     * @inheritDoc
     */
    public function applyInverse(array|object $item): array|object
    {
        return $this->applyToItem(
            $item,
            fn($value) => is_array($value) || is_object($value) ? $this->map->applyInverse($value) : $value
        );
    }

    /**
     * Apply map or inverse map to item.
     *
     * @template TItem
     * @phpstan-param TItem&(array<string,object>|object) $item
     * @phpstan-param callable(mixed $value):mixed        $apply
     * @phpstan-return TItem&(array<string,object>|object)
     */
    protected function applyToItem(array|object $item, callable $apply): array|object
    {
        if (!DotKey::on($item)->exists($this->field)) {
            return $item;
        }

        if ($this->isForMany) {
            $apply = fn($value) => is_iterable($value) ? Pipeline::with($value)->map($apply)->toArray() : $value;
        }

        $value = DotKey::on($item)->get($this->field);
        $newValue = $apply($value);

        return $this->updateItem($item, $value, $newValue);
    }

    /**
     * Set new value for item, if value has changed.
     *
     * @template TItem
     * @phpstan-param TItem&(array<string,object>|object) $item
     * @phpstan-param mixed                               $value
     * @phpstan-param mixed                               $newValue
     * @phpstan-return TItem&(array<string,object>|object)
     */
    protected function updateItem(array|object $item, mixed $value, mixed $newValue): array|object
    {
        // Not changed or same object
        if ($value === $newValue) {
            return $item;
        }

        if ($value instanceof \ArrayObject) {
            $array = i\iterable_to_array($newValue);
            $newValue = clone $value;
            $newValue->exchangeArray($array);
        }

        DotKey::onCopy($item, $copy)->set($this->field, $newValue);

        return $copy;
    }
}
