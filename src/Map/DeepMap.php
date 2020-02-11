<?php

declare(strict_types=1);

namespace Jasny\DB\Map;

use Jasny\DB\Filter\FilterItem;
use Jasny\DB\Update\UpdateInstruction;
use Jasny\DotKey\DotKey;

use function Jasny\str_starts_with;
use function Jasny\str_contains;

/**
 * Map that supports deep mapping through dot notation.
 *
 * @immutable
 */
final class DeepMap implements MapInterface
{
    /** @var array<string,string|false> */
    protected array $map;
    /** @var array<string,string> */
    protected array $inverse;

    /** @var array<string,string> */
    protected array $normalized;
    /** @var array<string,string> */
    protected array $valueMap;

    /**
     * Class constructor.
     *
     * @param array<string,string|false> $map
     */
    public function __construct(array $map)
    {
        $this->map = $map;
        $this->inverse = array_flip(array_filter($map));

        $this->normalize();
        $this->createValueMap();
    }


    /**
     * Normalize all field, replacing '/' delimiter with '.'.
     */
    protected function normalize(): void
    {
        foreach ($this->map as $appField => $dbField) {
            $appField = str_replace('/', '.', $appField);
            $dbField = is_string($dbField) ? str_replace('/', '.', $dbField) : $dbField;

            $this->normalized[$appField] = $dbField;
        }
    }

    /**
     * Create map for child values to be mapped for filter and update.
     */
    protected function createValueMap(): void
    {
        foreach ($this->map as $orgAppField => $orgDbField) {
            $appField = str_replace('/', '.', $orgAppField);
            $dbField = str_replace('/', '.', $orgDbField);
            $parts = explode('.', $appField);

            while (count($parts) > 1) {
                array_pop($parts);
                $path = join('.', $parts);
                $child = substr($orgAppField, strlen($path));
                $newPath = $this->normalized[$path] ?? $path;

                if (str_starts_with($dbField, $newPath . '.')) {
                    $dbField = str_contains($dbField, '/') substr($dbField, strlen($newPath) + 1);
                }

                $this->valueMap[$path][$child] = $dbField;
            }
        }
    }

    /**
     * Map App field to DB field.
     *
     * @param string $field
     * @return string|false
     */
    public function toDB(string $field)
    {
        // Exact mapping, common case quick return
        if (isset($this->normalized[$field])) {
            return $this->normalized[$field];
        }

        // No deep mapping and not mapped, common case quick return
        if (strpos($field, '.') === false && !isset($this->normalized[$field])) {
            return $field;
        }

        // Apply deep mapping
        $same = [];
        $parts = explode('.', $field);

        while (count($parts) > 1) {
            array_unshift($same, array_pop($parts));
            $key = join('.', $parts);

            if (!isset($this->normalized[$key])) {
                continue;
            }

            return $this->normalized[$key] !== false
                ? $this->normalized[$key] . ($same !== [] ? '.' . join('.', $same) : '')
                : false;
        }

        // Not mapped
        return $field;
    }

    /**
     * Apply mapping to filter items.
     *
     * @param iterable<FilterItem> $filterItems
     * @return iterable<FilterItem>
     */
    protected function applyToFilter(iterable $filterItems): iterable
    {
        foreach ($filterItems as $item) {
            $field = $item->getField();
            $newField = $this->toDB($field);

            $value = $item->getValue();
            $newValue = $this->mapValue($field, $newField, $value);

            if ($newField !== false) {
                continue;
            }

            yield $newField !== $field || $newValue !== $value
                ? new FilterItem($newField, $item->getOperator(), $newValue)
                : $item;
        };
    }

    /**
     * Apply mapping to update operations.
     *
     * @param iterable<UpdateInstruction> $update
     * @return iterable<UpdateInstruction>
     */
    protected function applyToUpdate(iterable $update): iterable
    {
        foreach ($update as $instruction) {
            $instruction = $this->applyToUpdateInstruction($instruction);

            if ($instruction !== null) {
                yield $instruction;
            }
        }
    }

    /**
     * Apply mapping to a single update instruction.
     */
    protected function applyToUpdateInstruction(UpdateInstruction $instruction): ?UpdateInstruction
    {
        $pairs = $instruction->getPairs();
        $mappedPairs = [];

        foreach ($pairs as $field => $value) {
            $newField = $this->toDB($field);
            $mappedPairs += $this->mapValue($field, $newField, $value);
        }

        if ($mappedPairs === $pairs) {
            return $instruction;
        }

        return $mappedPairs !== []
            ? new UpdateInstruction($instruction->getOperator(), $mappedPairs)
            : null;
    }

    /**
     * Map value.
     *
     * @param string       $field
     * @param string|false $newField
     * @param mixed        $value
     * @return array
     */
    protected function mapValue(string $field, $newField, $value): array
    {
        if ($newField === false) {
            return [];
        }

        if (!isset($this->expanded[$field]) || (!is_array($value) && !is_object($value))) {
            return [$newField => $value];
        }

        $set = [];
        $remove = [];
        $mapped = [];

        foreach ($this->expanded as $path => $childField) {
            if (!DotKey::on($value)->exists($path)) {
                continue;
            }

            $remove[] = $path;
            $value = DotKey::on($value)->get($path);

            if (str_starts_with($childField, $newField . '.')) {
                $set[substr($childField, strlen($newField) + 1)] = $value;
            } else {
                $mapped[$childField] = $value;
            }
        }

        return $mapped;
    }


    /**
     * Apply mapping to item.
     * Returns `null` if there are no changes.
     *
     * @param array<string,string>   $map
     * @param iterable<mixed> $items
     * @return iterable<mixed>
     */
    protected function applyToItems(array $map, iterable $items): iterable
    {
        foreach ($items as $key => $item) {
            if (!is_array($item) && !is_object($item)) {
                yield $key => $item;
                continue;
            }

            $set = [];
            $remove = [];

            foreach ($map as $field => $newField) {
                $path = str_replace('/', '.', $field);

                if (!DotKey::on($item)->exists($path)) {
                    continue;
                }

                if ($newField !== false) {
                    $set[$newField] = DotKey::on($item)->get($path);
                }
                $remove[] = explode('/', $field, 2)[0];
            }

            foreach ($remove as $field) {
                DotKey::on($item)->remove($field);
            }

            foreach ($set as $field => $value) {
                DotKey::on($item)->put($field, $value);
            }

            yield $key => $item;
        }
    }



    /**
     * @inheritDoc
     */
    public function forFilter(): callable
    {
        return \Closure::fromCallable([$this, 'applyToFilter']);
    }

    /**
     * @inheritDoc
     */
    public function forUpdate(): callable
    {
        return \Closure::fromCallable([$this, 'applyToUpdate']);
    }

    /**
     * @inheritDoc
     */
    public function forResult(): callable
    {
        $map = $this->inverse;
        return fn(iterable $result) => $this->applyToItems($map, $result);
    }

    /**
     * @inheritDoc
     */
    public function forItems(): callable
    {
        $map = $this->map;
        return fn(iterable $items) => $this->applyToItems($map, $items);
    }
}
