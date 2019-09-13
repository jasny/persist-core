<?php

declare(strict_types=1);

namespace Jasny\DB\QueryBuilder\Step;

use Improved as i;
use Jasny\DB\Exception\InvalidUpdateOperationException;
use Jasny\DB\Update\UpdateOperation;

/**
 * Convert update operations to iterator for query builder.
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
        $exception = new InvalidUpdateOperationException("Expected an UpdateOperation object; gotten %s");

        foreach ($operations as $operation) {
            /** @var UpdateOperation $operation */
            i\type_check($operation, UpdateOperation::class, $exception);

            $operator = $operation->getOperator();
            $pairs = $operation->getPairs();

            foreach ($pairs as $field => $value) {
                $info = ['field' => $field, 'operator' => $operator];
                yield $info => $value;
            }
        }
    }
}
