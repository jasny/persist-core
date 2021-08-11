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
     * @return Result<TItem>
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
     * @param TItem             $item
     * @param OptionInterface   ...$opts
     * @return Result<TItem>
     *
     * @phpstan-param TItem&(array<string,mixed>|object) $item
     * @phpstan-param OptionInterface                    ...$opts
     * @phpstan-return Result<TItem>
     */
    public function save(array|object $item, OptionInterface ...$opts): Result;

    /**
     * Save the multiple items.
     * Result contains generated properties for each item.
     *
     * @param iterable<TItem>   $items
     * @param OptionInterface   ...$opts
     * @return Result<TItem>
     */
    public function saveAll(iterable $items, OptionInterface ...$opts): Result;

    /**
     * Query and update records.
     *
     * @param array<string,mixed>|FilterItem[]      $filter
     * @param UpdateInstruction|UpdateInstruction[] $instructions
     * @param OptionInterface                       ...$opts
     * @return Result<TItem>
     */
    public function update(array $filter, UpdateInstruction|array $instructions, OptionInterface ...$opts): Result;

    /**
     * Query and delete records.
     *
     * @param array<string,mixed>|FilterItem[] $filter
     * @param OptionInterface                  ...$opts
     * @return Result<TItem>
     */
    public function delete(array $filter, OptionInterface ...$opts): Result;
}
