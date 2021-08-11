<?php

declare(strict_types=1);

namespace Jasny\Persist\Query;

use Jasny\Persist\Option\FieldsOption;
use Jasny\Persist\Option\HydrateOption;
use Jasny\Persist\Option\LookupOption;
use Jasny\Persist\Option\OptionInterface;
use function Jasny\array_without;
use function Jasny\str_contains;

/**
 * Inject hydrate and lookups that specified a `for` collection into their parent lookup option.
 *
 * @template TQuery
 * @implements ComposerInterface<TQuery,mixed,mixed>
 */
class NestLookup implements ComposerInterface
{
    /**
     * @inheritDoc
     */
    public function getPriority(): int
    {
        return 200;
    }

    /**
     * Apply items to given query.
     *
     * @template TItem
     * @param TQuery&object     $accumulator
     * @param iterable<TItem>   $items
     * @param OptionInterface[] $opts
     * @return iterable<TItem>
     */
    public function compose(object $accumulator, iterable $items, array &$opts = []): iterable
    {
        $injected = [];

        foreach ($opts as $key => $opt) {
            if ((!$opt instanceof LookupOption && !$opt instanceof HydrateOption) || $opt->getTarget() === null) {
                continue;
            }

            if ($this->injectLookup($opt, $opts)) {
                $injected[] = $key;
            }
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
     * @return bool
     */
    protected function injectLookup($lookup, array &$opts): bool
    {
        // Quick return for most common case.
        $target = $lookup->getTarget();
        if (!str_contains($target, '.')) {
            return $this->injectLookupForTarget($target, '', $lookup, $opts);
        }

        // Target is in the form of "a.b". It should be a sub-lookup or an embedded field.
        $field = [];
        $parts = explode('.', $target);

        while ($parts !== []) {
            if ($this->injectLookupForTarget(join('.', $parts), join('.', $field), $lookup, $opts)) {
                return true;
            }

            $field[] = array_pop($parts);
        };

        return false;
    }

    /**
     * Find an lookup or hydrate option to inject the lookup for this specific target.
     * The target might be an embedded relationship. In that case the lookup will be retargeted.
     *
     * @param string                     $target
     * @param string                     $field
     * @param LookupOption|HydrateOption $lookup
     * @param OptionInterface[]          $opts
     * @return bool
     */
    protected function injectLookupForTarget(
        string $target,
        string $field,
        LookupOption|HydrateOption $lookup,
        array &$opts
    ): bool {
        foreach ($opts as &$opt) {
            $found = ($opt instanceof LookupOption || $opt instanceof HydrateOption)
                && $opt !== $lookup
                && ($opt->getTarget() !== null ? $opt->getTarget() . '.' : '') . $opt->getName() === $target;

            if (!$found) {
                continue;
            }

            $opt = $opt->with(
                $lookup->for($field !== '' ? $field : null)
            );

            // Add the lookup field, if not specified in the projection.
            if ($this->isProjectionDefined($opt->getOpts())) {
                $opt = $opt->with(new FieldsOption([$lookup->getName()], false));
            }

            return true;
        }

        return false;
    }

    /**
     * Check if the option is only return specific fields.
     *
     * @param OptionInterface[] $opts
     * @return bool
     */
    protected function isProjectionDefined(array $opts): bool
    {
        foreach ($opts as $opt) {
            if ($opt instanceof FieldsOption && !$opt->isNegated()) {
                return true;
            }
        }

        return false;
    }
}
