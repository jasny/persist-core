<?php

declare(strict_types=1);

namespace Jasny\DB\QueryBuilder\Prepare;

use Improved as i;
use Jasny\DB\Option\FlagOption;
use Jasny\DB\Option\OptionInterface;

/**
 * Set the value to also be the key.
 * Not applied if the `preserve_keys` option is specified.
 */
class ItemAsKey
{
    /**
     * Invoke the parser
     *
     * @param iterable          $items
     * @param OptionInterface[] $opts
     * @return iterable
     */
    public function __invoke(iterable $items, array $opts): iterable
    {
        $preserveKeys = i\iterable_has_any(
            $opts,
            (fn($opt) => $opt instanceof FlagOption && $opt->getType() === 'preserve_keys')
        );

        return $preserveKeys
            ? $items
            : i\iterable_map_keys($items, fn($item) => $item);
    }
}
