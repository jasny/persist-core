<?php

declare(strict_types=1);

namespace Jasny\DB\QueryBuilder;

use Jasny\DB\Option\OptionInterface;
use Jasny\Immutable;

/**
 * Base class for filter and update query builders.
 * @internal
 *
 * @template TQueryItem
 * @implements QueryBuilderInterface<TQueryItem>
 */
abstract class AbstractQueryBuilder implements QueryBuilderInterface
{
    use Immutable\With;

    /** @var callable(iterable<TQueryItem>,OptionInterface[]):iterable<TQueryItem> */
    protected $prepare;
    /** @var callable(mixed,OptionInterface[]):void */
    protected $finalize;

    /**
     * Apply each element to the accumulator.
     *
     * @param object               $accumulator
     * @param iterable<TQueryItem> $iterable
     * @param OptionInterface[]    $opts
     */
    abstract protected function applyCompose(object $accumulator, iterable $iterable, array $opts = []): void;


    /**
     * AbstractQueryBuilder constructor.
     */
    public function __construct()
    {
        // nop functions
        $this->prepare = static function ($filterItems, array $options) {
            return $filterItems;
        };
        $this->finalize = static function ($accumulator, array $options): void {
        };
    }


    /**
     * Get the prepare logic of the query builder.
     *
     * @return callable(iterable<TQueryItem>,OptionInterface[]):iterable<TQueryItem>
     */
    public function getPreparation(): callable
    {
        return $this->prepare;
    }

    /**
     * Set the prepare logic of the query builder.
     *
     * @param callable(iterable<TQueryItem>,OptionInterface[]):iterable<TQueryItem> $prepare
     * @return static
     */
    public function withPreparation(callable $prepare): self
    {
        return $this->withProperty('prepare', $prepare);
    }


    /**
     * Get the finalize logic of the query builder.
     *
     * @return callable(mixed,OptionInterface[]):void
     */
    public function getFinalization(): callable
    {
        return $this->finalize;
    }

    /**
     * Set the finalize logic of the query builder.
     *
     * @param callable(mixed,OptionInterface[]):void $finalize
     * @return static
     */
    public function withFinalization(callable $finalize): self
    {
        return $this->withProperty('finalize', $finalize);
    }


    /**
     * Apply instructions to given query.
     *
     * @param object               $accumulator  Database specific query object.
     * @param iterable<TQueryItem> $iterable
     * @param OptionInterface[]    $opts
     */
    public function apply(object $accumulator, iterable $iterable, array $opts = []): void
    {
        $prepare = $this->getPreparation();
        $items = $prepare($iterable, $opts);

        $this->applyCompose($accumulator, $items, $opts);

        $finalize = $this->getFinalization();
        $finalize($accumulator, $opts);
    }
}
