<?php

declare(strict_types=1);

namespace Persist\Query;

use Persist\Map\MapInterface;
use Persist\Map\NoMap;
use Persist\Option\Functions as opt;
use Persist\Update\UpdateInstruction;

/**
 * Apply the field map to the update instructions.
 *
 * @template TQuery
 * @implements ComposerInterface<TQuery,UpdateInstruction>
 */
class ApplyMapToUpdate implements ComposerInterface
{
    /**
     * @inheritDoc
     * @throws \LogicException
     */
    public function compose(object $accumulator, iterable $items, array $opts = []): void
    {
        throw new \LogicException(__CLASS__ . ' can only be used in combination with other query composers');
    }

    /**
     * @inheritDoc
     */
    public function prepare(iterable $update, array &$opts = []): iterable
    {
        /** @var MapInterface $map */
        $map = opt\setting('map', new NoMap())->findIn($opts, MapInterface::class);

        // Quick return if there is no map
        if ($map instanceof NoMap) {
            return $update;
        }

        return $this->applyMap($map, $update);
    }

    /**
     * Apply mapping to update instructions.
     *
     * @param MapInterface                $map
     * @param iterable<UpdateInstruction> $update
     * @return \Generator&iterable<UpdateInstruction>
     */
    protected function applyMap(MapInterface $map, iterable $update): \Generator
    {
        foreach ($update as $key => $instruction) {
            $instruction = $this->map($map, $instruction);

            if ($instruction !== null) {
                yield $key => $instruction;
            }
        }
    }

    /**
     * Apply mapping to a single update instruction.
     */
    protected function map(MapInterface $map, UpdateInstruction $instruction): ?UpdateInstruction
    {
        $pairs = $instruction->getPairs();
        $mappedPairs = [];

        foreach ($pairs as $field => $value) {
            $mappedField = $map->applyToField($field) ?? $field;

            if ($mappedField !== false) {
                $mappedPairs[$mappedField] = $value;
            }
        }

        return ($mappedPairs === $pairs)
            ? $instruction
            : ($mappedPairs !== [] ? new UpdateInstruction($instruction->getOperator(), $mappedPairs) : null);
    }


    public function apply(object $accumulator, iterable $items, array $opts): iterable
    {
        return $items;
    }

    public function finalize(object $accumulator, array $opts): void
    {
    }
}
