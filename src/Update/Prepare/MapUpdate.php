<?php

declare(strict_types=1);

namespace Jasny\DB\Update\Prepare;

use Improved\IteratorPipeline\Pipeline;
use Jasny\DB\Map\MapInterface;
use Jasny\DB\Map\NoMap;
use Jasny\DB\Option\Functions as opts;
use Jasny\DB\Option\OptionInterface;
use Jasny\DB\Update\UpdateInstruction;

/**
 * Apply the field map to the update instructions.
 */
class MapUpdate
{
    /**
     * Invoke the map.
     *
     * @param UpdateInstruction[] $update
     * @param OptionInterface[]   $opts
     * @return UpdateInstruction[]
     */
    public function __invoke(array $update, array $opts): array
    {
        /** @var MapInterface|null $map */
        $map = opts\setting('map', new NoMap())->findIn($opts, MapInterface::class);

        // Quick return if there is no map
        if ($map instanceof NoMap) {
            return $update;
        }

        return Pipeline::with($update)
            ->map(fn($instruction) => $this->apply($map, $instruction))
            ->cleanup()
            ->toArray();
    }

    /**
     * Apply mapping to a single update instruction.
     */
    protected function apply(MapInterface $map, UpdateInstruction $instruction): ?UpdateInstruction
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
