<?php

declare(strict_types=1);

namespace Jasny\DB\FieldMap;

use Improved\IteratorPipeline\Pipeline;
use Jasny\DB\Filter\FilterItem;
use Jasny\DB\Update\UpdateInstruction;

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
    /** @var array<string, string> */
    protected array $inverse;

    /**
     * Class constructor.
     *
     * @param array<string, string> $map
     */
    public function __construct(array $map)
    {
        $this->map = $map;
        $this->inverse = array_flip($map);

        if (count($this->map) !== count($this->inverse)) {
            $duplicates = array_filter(array_count_values($this->map), fn($count) => $count > 1);
            throw new \UnexpectedValueException("Duplicate field in map: " . join(', ', array_keys($duplicates)));
        }
    }


    /**
     * Get field map as associative array.
     */
    public function getMap(): array
    {
        return $this->map;
    }

    /**
     * Get inverted field map as associative array.
     */
    public function getInverseMap(): array
    {
        return $this->inverse;
    }


    /**
     * Map an app field name to a DB field name.
     * Field name use dot notation.
     */
    public function toDB(string $appField): string
    {
        return $this->getDeepMapping($appField) ?? $appField;
    }

    /**
     * Map a DB field name to an app field name.
     */
    public function fromDB(string $dbField): string
    {
        return $this->map[$dbField] ?? $dbField;
    }


    /**
     * Get mapping for a nested field.
     *
     * @param string $field
     * @return string|null
     */
    protected function getDeepMapping(string $field): ?string
    {
        if (strpos($field, '.') === false) {
            return $this->inverse[$field] ?? null;
        }

        [$top, $nested] = explode('.', $field, 2);

        return isset($this->inverse[$top])
            ? $this->inverse[$top] . '.' . $nested
            : null;
    }

    /**
     * Apply mapping to filter items.
     *
     * @param FilterItem[] $filterItems
     * @return FilterItem[]
     */
    public function applyToFilter(array $filterItems): array
    {
        return Pipeline::with($filterItems)
            ->map(function (FilterItem $item): ?FilterItem {
                $field = $item->getField();
                $mapped = $this->getDeepMapping($field);

                return $mapped !== null
                    ? new FilterItem($mapped, $item->getOperator(), $item->getValue())
                    : $item;
            })
            ->toArray();
    }

    /**
     * Apply mapping to update operations.
     *
     * @param UpdateInstruction[] $update
     * @return UpdateInstruction[]
     */
    public function applyToUpdate(array $update): array
    {
        return Pipeline::with($update)
            ->map(\Closure::fromCallable([$this, 'applyToUpdateInstruction']))
            ->filter(fn($item) => $item !== null)
            ->toArray();
    }

    /**
     * Apply mapping to a single update instruction.
     */
    private function applyToUpdateInstruction(UpdateInstruction $instruction): ?UpdateInstruction
    {
        $pairs = $instruction->getPairs();
        $mappedPairs = [];

        foreach ($pairs as $field => $value) {
            $mapped = $this->getDeepMapping($field);
            $mappedPairs[$mapped ?? $field] = $value;
        }

        if ($mappedPairs === $pairs) {
            return $instruction;
        }

        return $mappedPairs !== []
            ? new UpdateInstruction($instruction->getOperator(), $mappedPairs)
            : null;
    }


    /**
     * Apply mapping to query result.
     *
     * @param iterable<array|object> $result
     * @return iterable<array|object>
     */
    public function applyToResult(iterable $result): iterable
    {
        foreach ($result as $key => $item) {
            yield $key => $this->applyMapToValue($this->map, $item);
        }
    }

    /**
     * Apply inverted mapping to items, so the data can be used by the DB.
     *
     * @param iterable<array|object> $items
     * @return iterable<array|object>
     */
    public function applyToItems(iterable $items): iterable
    {
        foreach ($items as $key => $item) {
            yield $key => $this->applyMapToValue($this->inverse, $item);
        }
    }


    /**
     * Invoke field map to apply mapping.
     *
     * @param array $map
     * @param mixed $subject
     * @return mixed
     */
    protected function applyMapToValue(array $map, $subject)
    {
        switch (true) {
            case is_array($subject):
                return $this->applyMapToArray($map, $subject);

            case $subject instanceof \ArrayObject:
                $mappedArray = $this->applyMapToArray($map, $subject->getArrayCopy());
                $subject->exchangeArray($mappedArray);
                return $subject;

            case is_object($subject):
                return $this->applyMapToObject($map, $subject);

            default:
                return $subject;
        }
    }

    /**
     * Apply mapping to keys of an associative array.
     * {@internal This method is optimized for performance, rather than readability.}}
     *
     * @param array<string,string> $map
     * @param array<string,mixed>  $subject
     */
    protected function applyMapToArray(array $map, array $subject): array
    {
        $copy = $subject; // Make a copy to deal with potential cross reference.
        $isChanged = false;

        foreach ($map as $field => $newField) {
            if (array_key_exists($field, $copy)) {
                $subject[$newField] = $copy[$field];
                $isChanged = true;
            }
        }

        return $isChanged ? array_diff_key($subject, array_diff($map, array_flip($map))) : $subject;
    }

    /**
     * Apply mapping to object properties.
     *
     * @param array<string,string> $map
     * @param object               $subject
     */
    protected function applyMapToObject(array $map, object $subject): object
    {
        $target = clone $subject;

        foreach ($map as $field => $newField) {
            if (property_exists($subject, $field)) {
                $target->{$newField} = $subject->{$field};
            }
        }

        $remove = array_diff(array_keys($map), $map);

        foreach ($remove as $prop) {
            unset($target->{$prop});
        }

        return $target;
    }


    /**
     * Allow a field map to cached using `var_export()`.
     * This method assumes the inverse is correct. No additional checks are done.
     *
     * @param array $data
     * @return static
     */
    public static function __set_state(array $data): self
    {
        if (!isset($data['map'])) {
            throw new \UnexpectedValueException("Unable to restore field map; corrupt data");
        }

        if (!isset($data['inverse'])) {
            // Shouldn't really happen
            return new static($data['map']);
        }

        $fieldMap = new static([]);
        $fieldMap->map = $data['map'];
        $fieldMap->inverse = $data['inverse'];

        return $fieldMap;
    }
}
