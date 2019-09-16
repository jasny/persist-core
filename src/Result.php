<?php

declare(strict_types=1);

namespace Jasny\DB;

use Improved as i;
use Improved\IteratorPipeline\Pipeline;
use UnexpectedValueException;

/**
 * Query result
 * @immutable
 */
class Result extends Pipeline
{
    protected array $meta;
    protected ?\Closure $metaFn;

    /**
     * Result constructor.
     *
     * @param iterable       $iterable
     * @param array|\Closure $meta
     */
    public function __construct(iterable $iterable = [], $meta = [])
    {
        parent::__construct($iterable);

        $this->setMeta($meta);
    }

    /**
     * Get a copy with new meta data.
     *
     * @param array|\Closure $meta
     * @return static
     */
    public function withMeta($meta): self
    {
        $clone = clone $this;
        $clone->setMeta($meta);

        return $clone;
    }

    /**
     * Set the meta data.
     *
     * @param array|\Closure $meta
     */
    protected function setMeta($meta): void
    {
        i\type_check($meta, ['array', \Closure::class]);

        unset($this->meta);
        $this->metaFn = null;

        if (is_array($meta)) {
            $this->meta = $meta;
        } else {
            $this->metaFn = $meta;
        }
    }

    /**
     * Get the metadata of the result
     *
     * @param string|null $key
     * @return array|mixed
     * @throws UnexpectedValueException if metadata closure didn't return an array or object
     */
    public function getMeta(?string $key = null)
    {
        if (isset($this->metaFn)) {
            $meta = ($this->metaFn)();
            i\type_check($meta, 'array', new UnexpectedValueException("Failed to get meta: Expected %2\$s, got %1\$s"));

            $this->meta = $meta;
            $this->metaFn = null;
        }

        return !isset($key) ? $this->meta : ($this->meta[$key] ?? null);
    }
}
