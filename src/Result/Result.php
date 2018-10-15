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
     * @var \stdClass|\Closure
     */
    protected $meta;


    /**
     * Result constructor.
     *
     * @param iterable                 $iterable
     * @param \stdClass|array|callable $meta
     */
    public function __construct(iterable $iterable, $meta = [])
    {
        expect_type($meta, [\stdClass::class, 'array', 'callable', 'null']);

        parent::__construct($iterable);

        $this->meta = $meta instanceof \stdClass ? clone $meta : (object)$meta;
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

        $msg = "Failed to get total count: Expected %2\$s, got %1\$s";
        expect_type($meta, [\stdClass::class, 'array'], \UnexpectedValueException::class, $msg);

        $this->meta = $meta instanceof \stdClass ? clone $meta : (object)$meta;
    }

    /**
     * Get the metadata of the result
     *
     * @return \stdClass
     * @throws \UnexpectedValueException if metadata closure didn't return an array or object
     */
    public function getMeta(): \stdClass
    {
        if (is_callable($this->meta)) {
            $this->resolveMeta();
        }

        return clone $this->meta;
    }
}
