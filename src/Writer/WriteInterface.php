<?php

declare(strict_types=1);

namespace Jasny\DB\Writer;

use Jasny\DB\Option\OptionInterface;
use Jasny\DB\Update\UpdateInstruction;
use Jasny\DB\Result\Result;
use Psr\Log\LoggerInterface;

/**
 * Service to add, update, and delete data from a persistent data storage (DB table, collection, etc).
 *
 * @template TItem
 */
interface WriteInterface
{
    /**
     * Get underlying storage object.
     * This is DB implementation dependent.
     *
     * @return mixed
     */
    public function getStorage();

    /**
     * Enable (debug) logging.
     *
     * @return static
     */
    public function withLogging(LoggerInterface $logger);


    /**
     * Save the one item.
     * Result contains generated properties for the item.
     *
     * @param array|object      $item
     * @param OptionInterface[] $opts
     * @return Result
     *
     * @phpstan-param TItem             $item
     * @phpstan-param OptionInterface[] $opts
     * @phpstan-return Result<TItem|\stdClass>
     */
    public function save($item, array $opts = []): Result;

    /**
     * Save the multiple items.
     * Result contains generated properties for each item.
     *
     * @param iterable<array|object> $items
     * @param OptionInterface[] $opts
     * @return Result
     *
     * @phpstan-param iterable<TItem>   $items
     * @phpstan-param OptionInterface[] $opts
     * @phpstan-return Result<TItem|\stdClass>
     */
    public function saveAll(iterable $items, array $opts = []): Result;

    /**
     * Query and update records.
     *
     * @param array<string,mixed>                   $filter
     * @param UpdateInstruction|UpdateInstruction[] $instructions
     * @param OptionInterface[]                     $opts
     * @return Result<TItem|\stdClass>
     */
    public function update(array $filter, $instructions, array $opts = []): Result;

    /**
     * Query and delete records.
     *
     * @param array<string, mixed> $filter
     * @param OptionInterface[]    $opts
     * @return Result<TItem|\stdClass>
     */
    public function delete(array $filter, array $opts = []): Result;
}
