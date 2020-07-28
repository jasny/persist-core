<?php

declare(strict_types=1);

namespace Jasny\DB\Query;

use Jasny\DB\Option\Functions as opt;
use Jasny\DB\Exception\LookupException;
use Jasny\DB\Option\LookupOption;
use function Jasny\array_without;

/**
 * Inject lookups that specified a `for` collection into their parent lookup option.
 */
class NestedLookup implements ComposerInterface
{
    /**
     * @inheritDoc
     */
    public function compose(object $accumulator, iterable $items, array $opts = []): void
    {
        throw new \LogicException(__CLASS__ . ' can only be used in combination with other query composers');
    }

    /**
     * @inheritDoc
     */
    public function prepare(iterable $items, array &$opts = []): iterable
    {
        $collection = null;
        $injected = [];

        foreach ($opts as $key => $opt) {
            if (!$opt instanceof LookupOption || $opt->getCollection() === null) {
                continue;
            }

            // Performance optimization.
            $collection ??= opt\setting('collection', '')->findIn($opts);
            if ($opt->getCollection() === $collection) {
                continue;
            }

            $this->injectLookup($opt, $opts);
            $injected[] = $key;
        }

        if ($injected !== []) {
            $opts = array_values(array_without($opts, $injected));
        }

        return $items;
    }

    /**
     * Inject deep lookups as opt in lookup of target collection.
     *
     * @param LookupOption $lookup
     * @param array        $opts
     */
    protected function injectLookup(LookupOption $lookup, array &$opts): void
    {
        $collection = $lookup->getCollection();

        foreach ($opts as &$opt) {
            if ($opt instanceof LookupOption && $opt->getRelated() === $collection && $opt !== $lookup) {
                $opt = $opt->with($lookup);
                return;
            }
        }

        throw new LookupException("Unable to apply lookup '" . $lookup->getRelated() . "'"
            . " for '{$collection}': no lookup for '{$collection}'");
    }

    /**
     * @inheritDoc
     */
    public function apply(object $accumulator, iterable $items, array $opts): iterable
    {
        return $items;
    }

    /**
     * @inheritDoc
     */
    public function finalize(object $accumulator, array $opts): void
    {
    }
}
