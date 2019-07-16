<?php

declare(strict_types=1);

namespace Jasny\DB;

use Improved as i;
use Improved\IteratorPipeline\Pipeline;
use LogicException;
use UnexpectedValueException;

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
        i\type_check($meta, ['array', 'callable']);

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
        i\type_check($meta, ['array', 'callable']);

        $clone = clone $this;
        $clone->meta = $meta;

        return $clone;
    }

    /**
     * Resolve metadata if it's still a callable.
     *
     * @return void
     * @throws UnexpectedValueException if metadata closure didn't return an aray
     */
    protected function resolveMeta(): void
    {
        i\type_check($this->meta, 'callable', new LogicException("Meta is not callable and thus can't be resolved"));

        $meta = ($this->meta)();
        i\type_check($meta, 'array', new UnexpectedValueException("Failed to get meta: Expected %2\$s, got %1\$s"));

        $this->meta = $meta;
    }

    /**
     * Get the metadata of the result
     *
     * @return array
     * @throws UnexpectedValueException if metadata closure didn't return an array or object
     */
    public function getMeta(): array
    {
        if (!is_array($this->meta)) {
            $this->resolveMeta();
        }

        return $this->meta;
    }
}
