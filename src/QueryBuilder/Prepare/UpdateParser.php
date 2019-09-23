<?php

declare(strict_types=1);

namespace Jasny\DB\QueryBuilder\Prepare;

use Improved as i;
use Jasny\DB\Exception\InvalidOperationException;
use Jasny\DB\Update\UpdateOperation;

/**
 * Convert update operations to iterator for query builder.
 * @immutable
 */
class UpdateParser
{
    /**
     * Convert an array of update operations into an iterable for the query builder.
     *
     * @param iterable<UpdateOperation> $operations
     * @return \Generator
     */
    public function __invoke(iterable $operations): \Generator
    {
        $exception = new InvalidOperationException("Expected an UpdateOperation object; gotten %s");

        foreach ($operations as $operation) {
            /** @var UpdateOperation $operation */
            i\type_check($operation, UpdateOperation::class, $exception);

            $operator = $operation->getOperator();
            $pairs = $operation->getPairs();

            foreach ($pairs as $field => $value) {
                yield (['field' => $field, 'operator' => $operator]) => $value;
            }
        }
    }
}
