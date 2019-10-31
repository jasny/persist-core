<?php

declare(strict_types=1);

namespace Jasny\DB\QueryBuilder;

use Jasny\DB\Option\OptionInterface;
use Jasny\Immutable;

/**
 * Base class for filter and update query builders.
 * @internal
 *
 * @method callable getPreparation()
 * @method void withPreparation(callable $preparation)
 */
abstract class AbstractQueryBuilder implements QueryBuilderInterface
{
    use Immutable\With;

    /** @var callable */
    protected $prepare;
    /** @var callable */
    protected $compose;
    /** @var callable */
    protected $finalize;

    /**
     * Apply each element to the accumulator.
     */
    abstract protected function applyCompose(object $accumulator, iterable $iterable, array $opts = []): void;


    /**
     * AbstractQueryBuilder constructor.
     */
    public function __construct(callable $compose)
    {
        $this->compose = $compose;

        // nop functions
        $this->prepare = static function (array $filterItems, array $options): array {
            return $filterItems;
        };
        $this->finalize = static function ($accumulator, array $options): void {
        };
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
     * @param object            $accumulator  Database specific query object.
     * @param iterable          $iterable
     * @param OptionInterface[] $opts
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
