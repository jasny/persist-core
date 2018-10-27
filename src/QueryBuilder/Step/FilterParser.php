<?php declare(strict_types=1);

namespace Jasny\DB\QueryBuilder\Step;

use Improved as i;
use Jasny\DB\Exception\InvalidFilterException;
use function Jasny\array_only;
use function Jasny\expect_type;

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
        return i\iterable_map_keys($filter, function($_, $key) {
            expect_type($key, 'string', InvalidFilterException::class);
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

        return array_only($matches, ['field', 'operator']) + ['operator' => ''];
    }
}
