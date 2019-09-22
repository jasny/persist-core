<?php

declare(strict_types=1);

namespace Jasny\DB\QueryBuilder;

use Jasny\DB\Exception\BuildQueryException;
use Jasny\DB\Option\OptionInterface;

/**
 * Interface for service that can apply instructions to a database specific query.
 */
interface QueryBuilderInterface
{
    /**
     * Apply instructions to given query.
     *
     * @param object            $accumulator  Database specific query object.
     * @param iterable          $iterable
     * @param OptionInterface[] $opts
     * @throws BuildQueryException
     */
    public function apply(object $accumulator, iterable $iterable, array $opts = []): void;

    /**
     * Alias of `apply()`.
     *
     * @param object            $accumulator  Database specific query object.
     * @param iterable          $iterable
     * @param OptionInterface[] $opts
     * @throws BuildQueryException
     */
    public function __invoke(object $accumulator, iterable $iterable, array $opts = []): void;
}
