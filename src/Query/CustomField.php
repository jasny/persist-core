<?php

declare(strict_types=1);

namespace Persist\Query;

use Persist\Option\FieldsOption;
use Persist\Option\OptionInterface;

/**
 * Add an expression as custom field when composing a query.
 *
 * @template TQuery
 * @implements ComposerInterface<TQuery,mixed>
 */
class CustomField implements ComposerInterface
{
    protected string $field;
    protected \Closure $callback;

    /**
     * @phpstan-param string                                                  $field
     * @phpstan-param callable(TQuery,FilterItem,array<OptionInterface>):void $callback
     */
    public function __construct(string $field, callable $callback)
    {
        $this->field = $field;
        $this->callback = \Closure::fromCallable($callback);
    }

    /**
     * @inheritDoc
     */
    public function compose(object $accumulator, iterable $items, array $opts = []): void
    {
        $this->apply($accumulator, $items, $opts);
    }

    /**
     * @inheritDoc
     */
    public function prepare(iterable $items, array &$opts = []): iterable
    {
        return $items;
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
        if ($this->isFieldIncluded($opts)) {
            ($this->callback)($accumulator, $opts);
        }
    }

    /**
     * Check if the field is included in the projection.
     *
     * @param OptionInterface[] $opts
     * @return bool
     */
    protected function isFieldIncluded(array $opts): bool
    {
        $default = true;
        $included = null;

        foreach ($opts as $opt) {
            if (!$opt instanceof FieldsOption) {
                continue;
            }

            $default = $default && $opt->isNegated();

            if (in_array($this->field, $opt->getFields(), true)) {
                $included = !$opt->isNegated();
            }
        }

        return $included ?? $default;
    }
}
