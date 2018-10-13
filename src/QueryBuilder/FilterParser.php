<?php

declare(strict_types=1);

namespace Jasny\DB\QueryBuilder;

/**
 * Parse a filter key, extracting the field and operator.
 * This turns the key into ['field' => string, 'operator' => string]
 */
class FilterParser
{
    protected const REGEXP = '/^\s*(?<field>[^\s(]+)\s*(?:\((?<operator>[^)]+)\)\s*)?$/';

    /**
     * Invoke the parser
     *
     * @param iterable $filter
     * @return \Generator
     */
    public function __invoke(iterable $filter): \Generator
    {
        foreach ($filter as $key => $value) {
            expect_type($key, 'string', \UnexpectedValueException::class);

            $info = strpos($key, '(') !== false && preg_match(static::REGEXP, $key, $matches)
                ? ($matches + ['operator' => null])
                : ['field' => trim($key), 'operator' => null];

            yield $info => $value;
        };
    }
}
