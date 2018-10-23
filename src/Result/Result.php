<?php

declare(strict_types=1);

namespace Jasny\DB\Result;

use Improved as i;
use Improved\IteratorPipeline\Pipeline;
use function Jasny\expect_type;

/**
 * Query result
 */
class Result extends Pipeline
{
    /**
     * @var array|\Closure
     */
    protected $meta;


    /**
     * Result constructor.
     *
     * @param iterable       $iterable
     * @param array|callable $meta
     */
    public function __construct(iterable $iterable = [], $meta = [])
    {
        expect_type($meta, ['array', 'callable']);

        parent::__construct($iterable);

        $this->meta = $meta;
    }

    /**
     * Get a copy with new meta data.
     *
     * @param array|callable $meta
     * @return static
     */
    public function withMeta($meta): self
    {
        expect_type($meta, ['array', 'callable']);

        $clone = clone $this;
        $clone->meta = $meta;

        return $clone;
    }

    /**
     * Resolve metadata if it's still a Closure.
     *
     * @return void
     * @throws \UnexpectedValueException if metadata closure didn't return a positive integer
     */
    protected function resolveMeta(): void
    {
        expect_type($this->meta, 'callable', \BadMethodCallException::class);

        $meta = i\function_call($this->meta);
        expect_type($meta, 'array', \UnexpectedValueException::class, "Failed to get meta: Expected %2\$s, got %1\$s");

        $this->meta = $meta;
    }

    /**
     * Get the metadata of the result
     *
     * @return array
     * @throws \UnexpectedValueException if metadata closure didn't return an array or object
     */
    public function getMeta(): array
    {
        if (!is_array($this->meta)) {
            $this->resolveMeta();
        }

        return $this->meta;
    }
}
