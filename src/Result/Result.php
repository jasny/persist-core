<?php

declare(strict_types=1);

namespace Persist\Result;

use Improved as i;
use Improved\IteratorPipeline\Pipeline;
use function Jasny\object_get_properties;
use function Jasny\object_set_properties;

/**
 * Query result.
 *
 * @template TValue
 */
class Result extends Pipeline
{
    /** @var array<string,mixed> */
    protected array $meta;

    /**
     * Result constructor.
     *
     * @param iterable<object|array<string,mixed>> $iterable
     * @param array<string,mixed>                  $meta
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


    /**
     * Apply result to given items.
     *
     * @param array|\ArrayAccess $items
     * @return $this
     *
     * @phpstan-param array<mixed,TValue>|\ArrayAccess<mixed,TValue> $items
     * @phpstan-return $this
     */
    public function applyTo($items): self
    {
        i\type_check(
            $items,
            ['array', \ArrayAccess::class],
            new \UnexpectedValueException("Unable to apply result to items. Expected %2s, %1s given")
        );

        $this->map(static function ($doc, $key) use ($items) {
            $item = $items[$key];

            if (is_object($doc)) {
                $doc = object_get_properties($doc, true);
            }

            if (is_array($item)) {
                $item = array_merge($item, $doc);
            } elseif (is_object($item) && is_callable([$item, '__unserialize'])) {
                $item->__unserialize($doc);
            } elseif (is_object($item)) {
                object_set_properties($item, $doc, $item instanceof \stdClass);
            }

            return $item;
        });

        return $this;
    }

    /**
     * Factory method for ResultBuilder
     */
    public static function build(): ResultBuilder
    {
        return new ResultBuilder(get_called_class());
    }
}
