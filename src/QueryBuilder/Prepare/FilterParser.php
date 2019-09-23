<?php

declare(strict_types=1);

namespace Jasny\DB\QueryBuilder\Prepare;

use Improved as i;
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
     */
    public function __invoke(iterable $filter): iterable
    {
        $exception = new \InvalidArgumentException("Expected filter key to be a string: %s given");

        return i\iterable_map_keys($filter, function ($_, $key) use ($exception) {
            i\type_check($key, 'string', $exception);
            return $this->parse($key);
        });
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
