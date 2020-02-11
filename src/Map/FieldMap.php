<?php

declare(strict_types=1);

namespace Jasny\DB\Map;

use Jasny\DB\Filter\FilterItem;
use Jasny\DB\Update\UpdateInstruction;
use Jasny\DotKey\DotKey;
use function Jasny\str_starts_with;

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
     * Map App field to DB field.
     *
     * @param string $field
     * @return string|false
     */
    public function toDB(string $field)
    {
        return $this->getMappedField($field) ?? $field;
    }

    /**
     * Get mapped field.
     * Return null if not mapped.
     *
     * @param string $field
     * @return string|false|null
     */
    private function getMappedField(string $field)
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
     * Apply mapping to filter items.
     *
     * @param iterable<FilterItem> $filterItems
     * @return iterable<FilterItem>
     */
    protected function applyToFilter(iterable $filterItems): iterable
    {
        foreach ($filterItems as $item) {
            $field = $item->getField();
            $mappedField = $this->getMappedField($field);

            yield $mappedField !== null && $mappedField !== false
                ? new FilterItem($mappedField, $item->getOperator(), $item->getValue())
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
            $mappedField = $this->getMappedField($field) ?? $field;

            if ($mappedField !== false) {
                $mappedPairs[$mappedField] = $value;
            }
        }

        if ($mappedPairs === $pairs) {
            return $instruction;
        }

        return $mappedPairs !== []
            ? new UpdateInstruction($instruction->getOperator(), $mappedPairs)
            : null;
    }

    /**
     * Apply mapping to item.
     * Returns `null` if there are no changes.
     *
     * @param array<string,string|false> $map
     * @param iterable<mixed>            $items
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
                if (!DotKey::on($item)->exists($field)) {
                    continue;
                }

                if ($newField !== false) {
                    $set[$newField] = DotKey::on($item)->get($field);
                }
                $remove[] = $field;
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
