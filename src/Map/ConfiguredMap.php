<?php

declare(strict_types=1);

namespace Jasny\DB\Map;

use function Jasny\str_contains;

/**
 * Configured field map.
 * Automatically creates a flat, deep, or nested map.
 */
final class ConfiguredMap implements MapInterface
{
    use Traits\ProxyTrait;

    /**
     * @param array<string,string|false|array> $map
     */
    public function __construct(array $map)
    {
        $isFlat = true;
        $nested = [];

        foreach ($map as $appField => $dbField) {
            if (is_array($dbField)) {
                $nested[$appField] = (new self($dbField))->getInnerMap();
                unset($map[$appField]);
                continue;
            }

            $isFlat = $isFlat
                && !str_contains($appField . $dbField, '.')
                && !str_contains($appField . $dbField, '/');
        }

        $inner = $map === [] ? new NoMap() : ($isFlat ? new FlatMap($map) : new DeepMap($map));

        if ($nested !== []) {
            $inner = new NestedMap($inner);

            foreach ($nested as $appField => $nestedMap) {
                $inner = $inner->withMappedField($appField, $nestedMap);
            }
        }

        $this->inner = $inner;
    }
}
