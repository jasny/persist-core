<?php

declare(strict_types=1);

namespace Persist\Query;

use Persist\Map\MapInterface;
use Persist\Map\NoMap;
use Persist\Option\Functions as opt;
use Persist\Option\OptionInterface;
use Persist\Update\UpdateInstruction;

/**
 * Apply the field map to the update instructions.
 *
 * @template TQuery
 * @implements ComposerInterface<TQuery,UpdateInstruction,UpdateInstruction>
 */
class ApplyMapToUpdate implements ComposerInterface
{
    /**
     * @inheritDoc
     */
    public function getPriority(): int
    {
        return 300;
    }

    /**
     * Apply items to given query.
     *
     * @param object                      $accumulator
     * @param iterable<UpdateInstruction> $instructions
     * @param OptionInterface[]           $opts
     * @return iterable
     *
     * @phpstan-param TQuery&object               $accumulator
     * @phpstan-param iterable<UpdateInstruction> $instructions
     * @phpstan-param OptionInterface[]           $opts
     * @phpstan-return iterable<UpdateInstruction>
     */
    public function compose(object $accumulator, iterable $instructions, array &$opts = []): iterable
    {
        /** @var MapInterface $map */
        $map = opt\setting('map', new NoMap())->findIn($opts, MapInterface::class);

        // Quick return if there is no map
        if ($map instanceof NoMap) {
            return $instructions;
        }

        return $this->applyMap($map, $instructions);
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
}
