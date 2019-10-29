<?php

declare(strict_types=1);

namespace Jasny\DB;

use Jasny\DB\Exception\UnsupportedFeatureException;
use Jasny\DB\Result\Result;
use Psr\Log\LoggerInterface;

/**
 * Writing to storage is not supported.
 * @immutable
 */
class NoWrite implements WriteInterface
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
    public function withLogging(LoggerInterface $logger): self
    {
        return $this;
    }


    /**
     * Update is not supported.
     *
     * @inheritDoc
     * @throws UnsupportedFeatureException
     */
    public function update(array $filter, array $changes, array $opts = []): Result
    {
        throw new UnsupportedFeatureException("Writing to storage is not supported");
    }

    /**
     * Save is not supported
     *
     * @inheritDoc
     * @throws UnsupportedFeatureException
     */
    public function save($item, array $opts = []): Result
    {
        throw new UnsupportedFeatureException("Writing to storage is not supported");
    }

    /**
     * Save is not supported
     *
     * @inheritDoc
     * @throws UnsupportedFeatureException
     */
    public function saveAll(iterable $items, array $opts = []): Result
    {
        throw new UnsupportedFeatureException("Writing to storage is not supported");
    }

    /**
     * Delete is not supported
     *
     * @inheritDoc
     * @throws UnsupportedFeatureException
     */
    public function delete(array $filter, array $opts = []): Result
    {
        throw new UnsupportedFeatureException("Writing to storage is not supported");
    }
}
