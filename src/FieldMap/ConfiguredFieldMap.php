<?php

declare(strict_types=1);

namespace Jasny\DB\FieldMap;

use Improved as i;
use Improved\IteratorPipeline\Pipeline;
use Jasny\DB\FieldMap;

/**
 * Map DB field to PHP field or visa versa.
 * Also works for parsed filters.
 */
class ConfiguredFieldMap implements FieldMap
{
    /**
     * @var array
     */
    protected $map;

    /**
     * @var bool
     */
    protected $dynamic;


    /**
     * Class constructor.
     *
     * @param array $map
     * @param bool  $dynamic Allow properties that are not mapped?
     */
    public function __construct(array $map, bool $dynamic = true)
    {
        $this->map = $map;
        $this->dynamic = $dynamic;
    }

    /**
     * Allow properties that are not mapped?
     *
     * @return bool
     */
    public function isDynamic(): bool
    {
        return $this->dynamic;
    }

    /**
     * Get field map as associative array
     *
     * @return array
     */
    public function toArray(): array
    {
        return $this->map;
    }


    /**
     * Get the inverse of the map.
     *
     * @return static
     */
    public function flip(): self
    {
        return new static(array_flip($this->map), $this->dynamic);
    }

    /**
     * Apply mapping to each key in the iterator/array.
     *
     * If the key is a string, it's replaced with the mapped field.
     * If the key is an array with a 'field' item, the value of the `field` item is replaced.
     *
     * @param iterable $iterable
     * @return iterable
     */
    protected function apply(iterable $iterable): iterable
    {
        return Pipeline::with($iterable)
            ->mapKeys(function ($_, $info) {
                $field = is_array($info) ? ($info['field'] ?? null) : $info;
                $newField = $this->map[$field] ?? ($this->dynamic ? $field : null);

                return isset($newField) && is_array($info) ? ['field' => $newField] + $info : $newField;
            })
            ->filter(function ($_, $info) {
                return $info !== null;
            });
    }

    /**
     * Invoke field map to apply mapping.
     *
     * @param iterable $iterable
     * @return iterable
     */
    public function __invoke(iterable $iterable): iterable
    {
        $mapped = $this->apply($iterable);

        return is_array($iterable) ? i\iterable_to_array($mapped, true) : $mapped;
    }


    /**
     * Check if a field is mapped.
     *
     * @param string $field
     * @return bool
     */
    public function offsetExists($field): bool
    {
        return isset($this->map[$field]);
    }

    /**
     * Get the mapping for a field.
     *
     * @param string $field
     * @return string|null
     */
    public function offsetGet($field): ?string
    {
        return $this->map[$field] ?? null;
    }

    /**
     * @param mixed $field
     * @param mixed $value
     * @throws \LogicException
     */
    public function offsetSet($field, $value): void
    {
        throw new \LogicException("FieldMap is immutable");
    }

    /**
     * @param mixed $field
     * @throws \LogicException
     */
    public function offsetUnset($field): void
    {
        throw new \LogicException("FieldMap is immutable");
    }
}
