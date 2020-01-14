<?php

declare(strict_types=1);

namespace Jasny\DB\Result;

use Improved\IteratorPipeline\PipelineBuilder;
use Jasny\DB\FieldMap\FieldMapInterface;

/**
 * Pipeline builder for a query result.
 * @immutable
 *
 * @template TValue
 */
class ResultBuilder extends PipelineBuilder
{
    /**
     * ResultBuilder constructor.
     */
    public function __construct(?FieldMapInterface $fieldMap = null)
    {
        if (isset($fieldMap)) {
            $this->steps[] = [[$fieldMap, 'applyToResult'], []];
        }
    }

    /**
     * Create a result.
     *
     * @phpstan-param iterable<TValue>    $iterable
     * @phpstan-param array<string,mixed> $meta
     * @phpstan-return Result<TValue>
     */
    public function with(iterable $iterable, array $meta = []): Result
    {
        $result = new Result($iterable, $meta);

        foreach ($this->steps as [$callback, $args]) {
            $result->then($callback, ...$args);
        }

        return $result;
    }
}
