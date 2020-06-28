<?php

declare(strict_types=1);

namespace Jasny\DB\Query;

use Improved\IteratorPipeline\Pipeline;
use Jasny\DB\Map\MapInterface;
use Jasny\DB\Map\NoMap;
use Jasny\DB\Option\Functions as opts;
use Jasny\DB\Update\UpdateInstruction;

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
        $map = opts\setting('map', new NoMap())->findIn($opts, MapInterface::class);

        // Quick return if there is no map
        if ($map instanceof NoMap) {
            return $update;
        }

        return Pipeline::with($update)
            ->map(fn(UpdateInstruction $instruction) => $this->map($map, $instruction))
            ->cleanup();
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
