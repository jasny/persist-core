<?php

declare(strict_types=1);

namespace Jasny\Persist\Gateway;

use Jasny\Persist\Filter\FilterItem;
use Jasny\Persist\Option\OptionInterface;
use Jasny\Persist\Result\Result;
use Jasny\Persist\Update\UpdateInstruction;
use Psr\Log\LoggerInterface;

/**
 * Gateway to a database table or collection.
 *
 * @template TItem
 */
interface GatewayInterface
{
    /**
     * Enable (debug) logging.
     *
     * @return static
     */
    public function withLogging(LoggerInterface $logger);


    /**
     * Query and fetch data.
     *
     * @param array<string,mixed>|FilterItem[] $filter
     * @param OptionInterface                  ...$opts
     * @return Result
     *
     * @phpstan-return Result<TItem>
     */
    public function fetch(array $filter = [], OptionInterface ...$opts): Result;

    /**
     * Query and count result.
     *
     * @param array<string,mixed>|FilterItem[] $filter
     * @param OptionInterface                  ...$opts
     * @return int
     */
    public function count(array $filter = [], OptionInterface ...$opts): int;

    /**
     * Save the one item.
     * Result contains generated properties for the item.
     *
     * @param array|object      $item
     * @param OptionInterface   ...$opts
     * @return Result
     *
     * @phpstan-param TItem             $item
     * @phpstan-param OptionInterface   ...$opts
     * @phpstan-return Result<TItem>
     */
    public function save($item, OptionInterface ...$opts): Result;

    /**
     * Save the multiple items.
     * Result contains generated properties for each item.
     *
     * @param iterable<array|object> $items
     * @param OptionInterface        ...$opts
     * @return Result
     *
     * @phpstan-param iterable<TItem>   $items
     * @phpstan-param OptionInterface   ...$opts
     * @phpstan-return Result<TItem>
     */
    public function saveAll(iterable $items, OptionInterface ...$opts): Result;

    /**
     * Query and update records.
     *
     * @param array<string,mixed>|FilterItem[]      $filter
     * @param UpdateInstruction|UpdateInstruction[] $instructions
     * @param OptionInterface                       ...$opts
     * @return Result
     *
     * @phpstan-return Result<TItem>
     */
    public function update(array $filter, $instructions, OptionInterface ...$opts): Result;

    /**
     * Query and delete records.
     *
     * @param array<string,mixed>|FilterItem[] $filter
     * @param OptionInterface                  ...$opts
     * @return Result
     *
     * @phpstan-return Result<TItem>
     */
    public function delete(array $filter, OptionInterface ...$opts): Result;
}
