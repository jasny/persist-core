<?php

declare(strict_types=1);

namespace Jasny\Persist\Query;

use Jasny\Persist\Exception\LookupException;
use Jasny\Persist\Option\HydrateOption;
use Jasny\Persist\Option\LookupOption;
use Jasny\Persist\Option\OptionInterface;
use function Jasny\array_without;

/**
 * Inject hydrate and lookups that specified a `for` collection into their parent lookup option.
 */
class NestLookup implements ComposerInterface
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
        $injected = [];

        foreach ($opts as $key => $opt) {
            if ((!$opt instanceof LookupOption && !$opt instanceof HydrateOption) || $opt->getTarget() === null) {
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
     * @param LookupOption|HydrateOption $lookup
     * @param OptionInterface[]          $opts
     */
    protected function injectLookup($lookup, array &$opts): void
    {
        $target = $lookup->getTarget();

        foreach ($opts as &$opt) {
            if ((!$opt instanceof LookupOption && !$opt instanceof HydrateOption) || $opt === $lookup) {
                continue;
            }

            if (($opt->getTarget() !== null ? $opt->getTarget() . '.' : '') . $opt->getName() === $target) {
                $opt = $opt->with($lookup);
                return;
            }
        }

        $type = $lookup instanceof LookupOption ? 'lookup' : 'hydrate';
        throw new LookupException("Unable to apply {$type} '" . $lookup->getName() . "' for '{$target}'");
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
