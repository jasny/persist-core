<?php

declare(strict_types=1);

namespace Jasny\DB\Gateway;

use Jasny\DB\Exception\UnsupportedFeatureException;
use Jasny\DB\Option\OptionInterface;
use Jasny\DB\Result\Result;
use Psr\Log\LoggerInterface;

/**
 * Dummy gateway if read/write from storage is not supported.
 *
 * @implements GatewayInterface<null>
 */
final class Unsupported implements GatewayInterface
{
    /**
     * Get underlying storage object.
     *
     * @return null
     */
    public function getStorage()
    {
        return null;
    }

    /**
     * Does nothing.
     *
     * @param LoggerInterface $logger
     * @return $this
     */
    public function withLogging(LoggerInterface $logger)
    {
        return $this;
    }

    /**
     * Fetch is not supported.
     *
     * @inheritDoc
     * @throws UnsupportedFeatureException
     */
    public function fetch(array $filter = [], OptionInterface ...$opts): Result
    {
        throw new UnsupportedFeatureException("Fetching from storage is not supported");
    }

    /**
     * Count is not supported.
     *
     * @inheritDoc
     * @throws UnsupportedFeatureException
     */
    public function count(array $filter = [], OptionInterface ...$opts): int
    {
        throw new UnsupportedFeatureException("Counting from storage is not supported");
    }

    /**
     * Update is not supported.
     *
     * @inheritDoc
     * @throws UnsupportedFeatureException
     */
    public function update(array $filter, $instruction, OptionInterface ...$opts): Result
    {
        throw new UnsupportedFeatureException("Updating to storage is not supported");
    }

    /**
     * Save is not supported
     *
     * @inheritDoc
     * @throws UnsupportedFeatureException
     */
    public function save($item, OptionInterface ...$opts): Result
    {
        throw new UnsupportedFeatureException("Saving to storage is not supported");
    }

    /**
     * Save is not supported
     *
     * @inheritDoc
     * @throws UnsupportedFeatureException
     */
    public function saveAll(iterable $items, OptionInterface ...$opts): Result
    {
        throw new UnsupportedFeatureException("Saving to storage is not supported");
    }

    /**
     * Delete is not supported
     *
     * @inheritDoc
     * @throws UnsupportedFeatureException
     */
    public function delete(array $filter, OptionInterface ...$opts): Result
    {
        throw new UnsupportedFeatureException("Deleting from storage is not supported");
    }
}
