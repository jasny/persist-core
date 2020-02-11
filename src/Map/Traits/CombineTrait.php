<?php

declare(strict_types=1);

namespace Jasny\DB\Map\Traits;

use Improved as i;
use Improved\IteratorPipeline\Pipeline;
use Jasny\DB\Map\MapInterface;

/**
 * Trait for maps that simply combine other maps.
 */
trait CombineTrait
{
    /** @var MapInterface[] $maps */
    protected array $maps;

    /**
     * Get the maps that are combined.
     *
     * @return MapInterface[]
     */
    public function getInnerMaps(): array
    {
        return $this->maps;
    }

    /**
     * Map App field to DB field.
     *
     * @param string $field
     * @return string|false
     */
    public function toDB(string $field)
    {
        foreach ($this->maps as $map) {
            $field = $map->toDB($field);

            if ($field === false) {
                break;
            }
        }

        return $field;
    }

    /**
     * Get function to apply mapping to filter items.
     *
     * @return callable(iterable<FilterItem>):iterable<FilterItem>
     */
    public function forFilter(): callable
    {
        return $this->pipe($this->maps, fn(MapInterface $map) => $map->forFilter());
    }

    /**
     * Get function to apply mapping to update operations.
     *
     * @return callable(iterable<UpdateInstruction>):iterable<UpdateInstruction>
     */
    public function forUpdate(): callable
    {
        return $this->pipe($this->maps, fn(MapInterface $map) => $map->forUpdate());
    }

    /**
     * Get function to apply mapping to query result.
     *
     * @return callable(iterable):iterable
     *
     * @template TItem
     * @phpstan-return callable(iterable<TItem>):iterable<TItem>
     */
    public function forResult(): callable
    {
        return $this->pipe(array_reverse($this->maps), fn(MapInterface $map) => $map->forResult());
    }

    /**
     * Get function to apply mapping to items, so the data can be used by the DB.
     *
     * @return callable(iterable):iterable
     *
     * @template TItem
     * @phpstan-return callable(iterable<TItem>):iterable<TItem>
     */
    public function forItems(): callable
    {
        return $this->pipe($this->maps, fn(MapInterface $map) => $map->forItems());
    }

    /**
     * Return a pipelined function for all maps.
     *
     * @param array<MapInterface> $maps
     * @param callable            $fn
     * @return callable
     */
    private function pipe(array $maps, callable $fn): callable
    {
        $functions = Pipeline::with($maps)->map($fn)->values()->toArray();

        return i\function_pipe(...$functions);
    }
}
