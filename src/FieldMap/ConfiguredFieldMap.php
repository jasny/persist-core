<?php

declare(strict_types=1);

namespace Jasny\DB\FieldMap;

use Improved\IteratorPipeline\Pipeline;

/**
 * Map DB field to PHP field or visa versa. Also works for parsed filters.
 * For results, the result builder should map each item to the field map.
 *
 * @immutable
 */
class ConfiguredFieldMap implements FieldMapInterface
{
    /** @var array<string, string> */
    protected array $map;
    protected bool $dynamic;

    /**
     * Class constructor.
     *
     * @param array<string, string> $map
     * @param bool                  $dynamic  Allow properties that are not mapped?
     */
    public function __construct(array $map, bool $dynamic = true)
    {
        $this->map = $map;
        $this->dynamic = $dynamic;
    }

    /**
     * Allow properties that are not mapped?
     */
    public function isDynamic(): bool
    {
        return $this->dynamic;
    }

    /**
     * Get field map as associative array.
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
    public function flip()
    {
        return new static(array_flip($this->map), $this->dynamic);
    }


    /**
     * Invoke field map to apply mapping.
     *
     * @param mixed $subject
     * @return mixed
     */
    public function __invoke($subject)
    {
        switch (true) {
            case is_array($subject):
                return $this->applyToArray($subject);

            case $subject instanceof \ArrayObject:
                $mappedArray = $this->applyToArray($subject->getArrayCopy());
                $subject->exchangeArray($mappedArray);
                return $subject;

            case $subject instanceof \Traversable:
                return $this->applyToIterator($subject);

            case is_object($subject):
                return $this->applyToObject($subject);
        }

        return $subject;
    }

    /**
     * Apply mapping to keys of an associative array.
     * {@internal This method is optimized for performance, rather than readability.}}
     *
     * @param array<string, mixed> $subject
     */
    protected function applyToArray(array $subject): array
    {
        $copy = $subject; // Make a copy to deal with potential cross reference.
        $isChanged = false;

        foreach ($this->map as $field => $newField) {
            if (array_key_exists($field, $copy)) {
                $subject[$newField] = $copy[$field];
                $isChanged = true;
            }
        }

        return $this->dynamic
            ? ($isChanged ? array_diff_key($subject, array_diff($this->map, array_flip($this->map))) : $subject)
            : array_intersect_key($subject, array_flip($this->map));
    }

    /**
     * Apply mapping to object properties.
     */
    protected function applyToObject(object $subject): object
    {
        $copy = clone $subject; // Make a copy to deal with potential cross reference.

        foreach ($this->map as $field => $newField) {
            if (property_exists($copy, $field)) {
                $subject->{$newField} = $copy->{$field};
            }
        }

        $remove = $this->dynamic
            ? array_diff(array_keys($this->map), $this->map)
            : array_diff(array_keys(get_object_vars($subject)), $this->map);

        foreach ($remove as $prop) {
            unset($subject->{$prop});
        }

        return $subject;
    }

    /**
     * Apply mapping to each key in the iterator.
     *
     * If the key is a string, it's replaced with the mapped field.
     * If the key is an array with a 'field' item, the value of the `field` item is replaced.
     */
    protected function applyToIterator(\Traversable $iterable): \Traversable
    {
        return Pipeline::with($iterable)
            ->mapKeys(function ($_, $info) {
                $field = is_array($info) ? ($info['field'] ?? null) : $info;
                $newField = $this->map[$field] ?? ($this->dynamic ? $field : null);

                return isset($newField) && is_array($info) ? ['field' => $newField] + $info : $newField;
            })
            ->filter(fn($_, $info) => $info !== null);
    }


    /**
     * Check if a field is mapped.
     *
     * @param string|mixed $field
     * @return bool
     */
    public function offsetExists($field): bool
    {
        return is_string($field) && isset($this->map[$field]);
    }

    /**
     * Get the mapping for a field.
     *
     * @param string|mixed $field
     * @return string|null
     */
    public function offsetGet($field): ?string
    {
        return is_string($field) && isset($this->map[$field]) ? $this->map[$field] : null;
    }

    /**
     * @internal
     * @param mixed $field
     * @param mixed $value
     * @throws \LogicException
     */
    public function offsetSet($field, $value): void
    {
        throw new \LogicException("Field map is immutable");
    }

    /**
     * @internal
     * @param mixed $field
     * @throws \LogicException
     */
    public function offsetUnset($field): void
    {
        throw new \LogicException("Field map is immutable");
    }
}
