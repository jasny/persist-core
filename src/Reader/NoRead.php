<?php

declare(strict_types=1);

namespace Jasny\DB\Reader;

use Jasny\DB\Exception\UnsupportedFeatureException;
use Jasny\DB\QueryBuilder\QueryBuilderInterface;
use Jasny\DB\Result\Result;
use Jasny\DB\Result\ResultBuilder;
use Psr\Log\LoggerInterface;

/**
 * Reading from storage is not supported.
 * @immutable
 */
class NoRead implements ReadInterface
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
     * Fetch is not supported.
     *
     * @throws UnsupportedFeatureException
     */
    public function fetch(array $filter = null, array $opts = []): Result
    {
        throw new UnsupportedFeatureException("Reading from storage is not supported");
    }

    /**
     * Count is not supported.
     *
     * @throws UnsupportedFeatureException
     */
    public function count(array $filter = null, array $opts = []): int
    {
        throw new UnsupportedFeatureException("Reading from storage is not supported");
    }
}
