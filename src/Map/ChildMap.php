<?php

declare(strict_types=1);

namespace Jasny\DB\Map;

use Improved as i;
use Jasny\DB\Filter\FilterItem;
use Jasny\DB\Update\UpdateInstruction;

use Jasny\DotKey\DotKey;
use function Jasny\str_starts_with;
use function Jasny\str_ends_with;

/**
 * Field map for a nested item.
 */
class ChildMap implements MapInterface
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
    public function getInnerMap(): MapInterface
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
    public function toDB(string $field)
    {
        if (!str_starts_with($field, $this->field . '.')) {
            return $field;
        }

        $mappedChildField = $this->map->toDB(substr($field, strlen($this->field) + 1));

        return $mappedChildField !== false ? $this->field . '.' . $mappedChildField : false;
    }

    /**
     * Apply mapping to filter items.
     *
     * @param callable(iterable):iterable $applyTo
     * @param iterable<FilterItem>        $filterItems
     * @return iterable<FilterItem>
     */
    protected function applyToFilter(callable $applyTo, iterable $filterItems): iterable
    {
        foreach ($filterItems as $item) {
            $field = $item->getField();

            if (str_starts_with($field, $this->field . '.')) {
                yield from $this->applyToFilterField($applyTo, $item);
            } else {
                yield $item;
            }
        }
    }

    /**
     * Apply mapping to the field of the filter item.
     *
     * @param callable $applyTo
     * @param FilterItem $item
     * @return \Generator
     */
    protected function applyToFilterField(callable $applyTo, FilterItem $item): \Generator
    {
        $childField = substr($item->getField(), strlen($this->field) + 1);
        $childItem = new FilterItem($childField, $item->getOperator(), $item->getValue());

        /** @var FilterItem $mappedItem */
        foreach ($applyTo([$childItem]) as $mappedItem) {
            if ($mappedItem === $childItem) {
                yield $item; // No change to child, can yield original
                continue;
            }

            $parentField = $this->field . '.' . $mappedItem->getField();
            yield new FilterItem($parentField, $childItem->getOperator(), $childItem->getValue());
        }
    }

    /**
     * Apply mapping to update operations.
     *
     * @param callable(UpdateInstruction):UpdateInstruction $applyTo
     * @param iterable<UpdateInstruction>                   $update
     * @return iterable<UpdateInstruction>
     */
    protected function applyToUpdate(callable $applyTo, iterable $update): iterable
    {
        foreach ($update as $instruction) {
            [$parentPairs, $childPairs] = $this->splitUpdatePairs($instruction->getPairs());

            if ($childPairs === []) {
                yield $instruction;
                continue;
            }

            $childInstruction = new UpdateInstruction($instruction->getOperator(), $childPairs);
            $mappedInstruction = $applyTo($childInstruction);

            if ($mappedInstruction === null) {
                continue;
            }

            yield $mappedInstruction === $childInstruction
                ? $instruction // No changes to the child fields, so we can yield the original
                : $this->createUpdateInstruction($mappedInstruction, $parentPairs);
        }
    }

    /**
     * Split pairs of update instruction into child and parent pairs.
     *
     * @param array<string,mixed> $pairs
     * @return array{array<string,mixed>,array<string,mixed>}
     */
    protected function splitUpdatePairs(array $pairs)
    {
        $parentPairs = [];
        $childPairs = [];

        foreach ($pairs as $field => $value) {
            if (!str_starts_with($field, $this->field . '.')) {
                $parentPairs[$field] = $value;
            } else {
                $childField = substr($field, strlen($this->field) + 1);
                $childPairs[$childField] = $value;
            }
        }

        return [$parentPairs, $childPairs];
    }

    /**
     * Apply mapping to a single update instruction.
     *
     * @param UpdateInstruction   $instruction
     * @param array<string,mixed> $parentPairs
     * @return UpdateInstruction|null
     */
    protected function createUpdateInstruction(UpdateInstruction $instruction, array $parentPairs): ?UpdateInstruction
    {
        foreach ($instruction->getPairs() as $childField => $value) {
            $parentPairs[$this->field . '.' . $childField] = $value;
        }

        return $parentPairs !== []
            ? new UpdateInstruction($instruction->getOperator(), $parentPairs)
            : null;
    }


    /**
     * Apply mapping to items (from DB or to DB).
     *
     * @param callable        $applyTo
     * @param iterable<mixed> $items
     * @return iterable<mixed>
     */
    protected function applyToItems(callable $applyTo, iterable $items): iterable
    {
        foreach ($items as $key => $item) {
            if ((!is_array($item) && !is_object($item)) || !DotKey::on($item)->exists($this->field)) {
                yield $key => $item; // Field not present
                continue;
            }

            $value = DotKey::on($item)->get($this->field);
            $newValue = $applyTo($value);

            if ($value === $newValue) {
                // Not changed or value is an object
            } elseif ($value instanceof \ArrayObject) {
                $value->exchangeArray(i\iterable_to_array($newValue));
            } else {
                DotKey::on($item)->set($this->field, $newValue);
            }

            yield $item;
        }
    }


    /**
     * @inheritDoc
     */
    public function forFilter(): callable
    {
        $applyTo = $this->map->forFilter();
        return fn(iterable $filter): iterable => $this->applyToFilter($applyTo, $filter);
    }

    /**
     * @inheritDoc
     */
    public function forUpdate(): callable
    {
        $applyToIterable = $this->map->forUpdate();
        $applyTo = fn(UpdateInstruction $instruction) => i\iterable_first($applyToIterable([$instruction]));

        return fn(iterable $update): iterable => $this->applyToUpdate($applyTo, $update);
    }

    /**
     * @inheritDoc
     */
    public function forResult(): callable
    {
        return $this->forItemsApplyToChild($this->map->forResult());
    }

    /**
     * @inheritDoc
     */
    public function forItems(): callable
    {
        return $this->forItemsApplyToChild($this->map->forItems());
    }

    /**
     * Return callback to apply function to each child.
     *
     * @param callable $applyToIterable
     * @return \Closure
     */
    private function forItemsApplyToChild(callable $applyToIterable)
    {
        $applyTo = $this->isForMany
            ? fn($items) => i\iterable_to_array($applyToIterable($items), true)
            : fn($item) => i\iterable_first($applyToIterable([$item]));

        return fn(iterable $result): iterable => $this->applyToItems($applyTo, $result);
    }
}
