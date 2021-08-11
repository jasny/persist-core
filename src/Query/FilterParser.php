<?php

declare(strict_types=1);

namespace Jasny\Persist\Query;

use Jasny\Persist\Filter\FilterItem;
use Jasny\Persist\Option\OptionInterface;

/**
 * Parse a filter key into a basic filter by extracting the field and operator.
 * Format is "key" or "key (operator)".
 *
 * @template TQuery
 * @implements ComposerInterface<TQuery,mixed,FilterItem>
 */
class FilterParser implements ComposerInterface
{
    protected const REGEXP = '/^\s*(?<field>[^\s\(\)]+)\s*(?:\(\s*(?<operator>[^\(\)]*?)\s*\)\s*)?$/';

    /**
     * @inheritDoc
     */
    public function getPriority(): int
    {
        return 100;
    }

    /**
     * Apply items to given query.
     *
     * @param TQuery&object                               $accumulator
     * @param iterable<FilterItem>|iterable<string,mixed> $filter
     * @param OptionInterface[]                           $opts
     * @return iterable<FilterItem>
     */
    public function compose(object $accumulator, iterable $filter, array &$opts = []): iterable
    {
        foreach ($filter as $key => $value) {
            if ($value instanceof FilterItem) {
                yield $value;
            } else {
                ['field' => $field, 'operator' => $operator] = $this->parse($key);
                yield new FilterItem($field, $operator, $value);
            }
        }
    }

    /**
     * Parse the key into field and operator.
     *
     * @param string $key
     * @return array{field:string,operator:string}
     */
    protected function parse(string $key): array
    {
        // Quick return for field without an operator
        if (!str_contains($key, '(') && !str_contains($key, ')')) {
            return ['field' => trim($key), 'operator' => ''];
        }

        // Use regex to parse field and operator
        $result = preg_match(static::REGEXP, $key, $matches);

        if (!(bool)$result) {
            throw new \UnexpectedValueException("Invalid filter item '$key': Bad use of parentheses");
        }

        return [
            'field' => $matches['field'],
            'operator' => $matches['operator'] ?? '',
        ];
    }
}
