<?php declare(strict_types=1);

namespace Jasny\DB\Update;

use Jasny\DB\Exception\InvalidUpdateOperationException;
use function Jasny\expect_type;

/**
 * Convert update operations to iterator for query builder
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
        foreach ($operations as $operation) {
            /** @var UpdateOperation $operation */
            expect_type($operation, UpdateOperation::class, InvalidUpdateOperationException::class);

            $field = $operation->getField();
            $operator = $operation->getOperator();

            if (is_array($field)) {
                foreach ($field as $key => $value) {
                    $info = ['field' => $key, 'operator' => $operator];
                    yield $info => $value;
                }
            } else {
                $info = ['field' => $field, 'operator' => $operator];
                yield $info => $operation->getValue();
            }
        }
    }
}
