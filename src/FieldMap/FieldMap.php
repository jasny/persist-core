<?php

declare(strict_types=1);

namespace Jasny\DB\FieldMap;

use function Improved\iterable_to_array;

/**
 * Map DB field to PHP field or visa versa.
 * Also works for parsed filters.
 */
class FieldMap implements FieldMapInterface
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
     * Apply mapping.
     *
     * @param iterable $iterable
     * @return \Generator
     */
    protected function apply(iterable $iterable): iterable
    {
        foreach ($iterable as $info => $value) {
            $field = $info['field'] ?? $info;

            if (isset($this->map[$field])) {
                $mapped = $this->map[$field];
                $info = is_array($info) ? ['field' => $mapped] + $info : $mapped;
            } elseif (!$this->dynamic) {
                continue;
            }

            yield $info => $value;
        }
    }

    /**
     * Apply mapping.
     *
     * @param iterable $iterable
     * @return iterable
     */
    public function __invoke(iterable $iterable): iterable
    {
        $mapped = $this->apply($iterable);

        return is_array($iterable) ? iterable_to_array($mapped, true) : $mapped;
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
     * @throws \BadMethodCallException
     */
    public function offsetSet($field, $value): void
    {
        throw new \BadMethodCallException("FieldMap is immutable");
    }

    /**
     * @param mixed $field
     * @throws \BadMethodCallException
     */
    public function offsetUnset($field): void
    {
        throw new \BadMethodCallException("FieldMap is immutable");
    }
}
