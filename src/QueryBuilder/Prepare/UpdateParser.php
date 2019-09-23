<?php

declare(strict_types=1);

namespace Jasny\DB\QueryBuilder\Prepare;

use Improved as i;
use Jasny\DB\Option\OptionInterface;
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
     * @param iterable          $operations
     * @param OptionInterface[] $opts
     * @return \Generator
     */
    public function __invoke(iterable $operations, array $opts): \Generator
    {
        $exception = new \UnexpectedValueException("Expected an UpdateOperation object; gotten %s");

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
