<?php

declare(strict_types=1);

namespace Jasny\DB\Result;

use Improved\IteratorPipeline\Pipeline;

/**
 * Query result.
 * @immutable
 */
class Result extends Pipeline
{
    protected array $meta;

    /**
     * Result constructor.
     *
     * @param iterable $iterable
     * @param array    $meta
     */
    public function __construct(iterable $iterable = [], array $meta = [])
    {
        parent::__construct($iterable);

        $this->meta = $meta;
    }

    /**
     * Get the metadata of the result.
     *
     * @param null|string $key  Omit the key to get all metadata.
     * @return array|mixed
     */
    public function getMeta(?string $key = null)
    {
        return !isset($key) ? $this->meta : ($this->meta[$key] ?? null);
    }
}
