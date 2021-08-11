<?php

declare(strict_types=1);

namespace Jasny\Persist\Gateway;

use Jasny\Persist\Exception\UnsupportedFeatureException;
use Jasny\Persist\Option\OptionInterface;
use Jasny\Persist\Result\Result;
use Psr\Log\LoggerInterface;

/**
 * Dummy gateway if read/write from storage is not supported.
 *
 * @implements GatewayInterface<null>
 */
final class Unsupported implements GatewayInterface
{
    /**
     * Does nothing.
     *
     * @return $this
     */
    public function withLogging(LoggerInterface $logger): self
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
