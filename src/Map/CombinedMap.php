<?php

declare(strict_types=1);

namespace Persist\Map;

use Jasny\Immutable;

/**
 * Combine multiple field maps.
 * These are applied in sequence.
 */
final class CombinedMap implements MapInterface
{
    use Immutable\With;
    use Traits\CombineTrait;

    /**
     * StructuredFieldMap constructor.
     *
     * @param MapInterface[] $maps
     */
    public function __construct(MapInterface ...$maps)
    {
        $this->maps = $maps;
    }

    /**
     * @inheritDoc
     */
    public function withOpts(array $opts): MapInterface
    {
        return $this;
    }
}
