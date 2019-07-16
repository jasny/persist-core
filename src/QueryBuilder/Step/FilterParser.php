<?php

declare(strict_types=1);

namespace Jasny\DB\QueryBuilder\Step;

use Improved as i;
use Jasny\DB\Exception\InvalidFilterException;

/**
 * Parse a filter key, extracting the field and operator.
 * This turns the key into ['field' => string, 'operator' => string]
 */
class FilterParser
{
    protected const REGEXP = '/^\s*(?<field>[^\s\(\)]+)\s*(?:\(\s*(?<operator>[^\(\)]*?)\s*\)\s*)?$/';

    /**
     * Invoke the parser
     *
     * @param iterable $filter
     * @return iterable
     */
    public function __invoke(iterable $filter): iterable
    {
        $exception = new InvalidFilterException("Expected filter key to be a string: %s given");

        return i\iterable_map_keys($filter, function ($_, $key) use ($exception) {
            i\type_check($key, 'string', $exception);
            return $this->parse($key);
        });
    }

    /**
     * Parse the key into field and operator.
     *
     * @param string $key
     * @return array
     */
    protected function parse(string $key): array
    {
        if (strpos($key, '(') === false && strpos($key, ')') === false) {
            return ['field' => trim($key), 'operator' => ''];
        }

        if (!preg_match(static::REGEXP, $key, $matches)) {
            throw new InvalidFilterException("Invalid filter item '$key': Bad use of parentheses");
        }

        return ['field' => $matches['field'], 'operator' => $matches['operator'] ?? ''];
    }
}
