<?php

declare(strict_types=1);

namespace Jasny\DB\Filter;

use Jasny\DB\Option\OptionInterface;
use Spatie\Regex\Regex;

/**
 * Parse a filter key into a basic filter by extracting the field and operator.
 * Format is "key" or "key (operator)".
 * @immutable
 */
class FilterParser
{
    protected const REGEXP = '/^\s*(?<field>[^\s\(\)]+)\s*(?:\(\s*(?<operator>[^\(\)]*?)\s*\)\s*)?$/';

    /**
     * Invoke the parser
     *
     * @param iterable          $filter
     * @param OptionInterface[] $opts
     * @return FilterItem[]
     */
    public function __invoke(iterable $filter, array $opts): array
    {
        $filterItems = [];

        foreach ($filter as $key => $value) {
            ['field' => $field, 'operator' => $operator] = $this->parse($key);
            $filterItems[] = new FilterItem($field, $operator, $value);
        }

        return $filterItems;
    }

    /**
     * Parse the key into field and operator.
     *
     * @return array{field:string, operator:string}
     */
    protected function parse(string $key): array
    {
        if (strpos($key, '(') === false && strpos($key, ')') === false) {
            return ['field' => trim($key), 'operator' => ''];
        }

        $result = Regex::match(static::REGEXP, $key);

        if (!$result->hasMatch()) {
            throw new \UnexpectedValueException("Invalid filter item '$key': Bad use of parentheses");
        }

        return [
            'field' => $result->group('field'),
            'operator' => $result->groupOr('operator', ''),
        ];
    }
}
