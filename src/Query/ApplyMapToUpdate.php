<?php

declare(strict_types=1);

namespace Jasny\Persist\Query;

use Jasny\Persist\Map\MapInterface;
use Jasny\Persist\Map\NoMap;
use Jasny\Persist\Option\Functions as opt;
use Jasny\Persist\Option\OptionInterface;
use Jasny\Persist\Update\UpdateInstruction;

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
     * @param TQuery&object               $accumulator
     * @param iterable<UpdateInstruction> $instructions
     * @param OptionInterface[]           $opts
     * @return iterable<UpdateInstruction>
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
