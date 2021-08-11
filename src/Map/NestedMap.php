<?php

declare(strict_types=1);

namespace Jasny\Persist\Map;

use Jasny\Persist\Option\OptionInterface;
use Jasny\Immutable;

/**
 * Field map for data structure (with embedded or child objects).
 */
final class NestedMap implements MapInterface
{
    use Immutable\With;
    use Traits\CombineTrait;

    /**
     * Class constructor.
     *
     * @param MapInterface|array<string,mixed> $map  Base map
     */
    public function __construct(MapInterface|array $map)
    {
        $this->maps[''] = $map instanceof MapInterface ? $map : new FieldMap($map);
    }

    /**
     * @inheritDoc
     */
    public function withOpts(array $opts): MapInterface
    {
        return $this;
    }

    /**
     * Get a copy with mapping for embedded items.
     *
     * @param string                                  $field
     * @param MapInterface|array<string,string|false> $map
     * @return self
     */
    public function withMappedField(string $field, MapInterface|array $map): self
    {
        $childMap = new ChildMap($field, $map);

        $maps = $this->maps;
        $maps[$childMap->getField()] = $childMap;
        krsort($maps);

        return $this->withProperty('maps', $maps);
    }
}
