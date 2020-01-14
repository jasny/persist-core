<?php

declare(strict_types=1);

namespace Jasny\DB\QueryBuilder;

use Jasny\DB\Option\OptionInterface;

/**
 * Query builder for save queries.
 *
 * @extends AbstractQueryBuilder<array<string,mixed>|object>
 */
class SaveQueryBuilder extends AbstractQueryBuilder
{
    /** @var callable(object,array<string,mixed>|object,mixed,array<OptionInterface>):void */
    protected $compose;

    /**
     * SaveQueryBuilder constructor.
     *
     * @param callable(object,array<string,mixed>|object,mixed,array<OptionInterface>):void $compose
     */
    public function __construct(callable $compose)
    {
        $this->compose = $compose;

        parent::__construct();
    }

    /**
     * Apply each item to the accumulator.
     *
     * @param object                               $accumulator
     * @param iterable<array<string,mixed>|object> $items
     * @param OptionInterface[]                    $opts
     */
    protected function applyCompose(object $accumulator, iterable $items, array $opts = []): void
    {
        foreach ($items as $index => $item) {
            ($this->compose)($accumulator, $item, $index, $opts);
        }
    }
}
