<?php

declare(strict_types=1);

namespace Jasny\Persist\Query;

use Jasny\Persist\Filter\FilterItem;
use Jasny\Persist\Option\OptionInterface;
use Spatie\Regex\Regex;

/**
 * Parse a filter key into a basic filter by extracting the field and operator.
 * Format is "key" or "key (operator)".
 *
 * @implements ComposerInterface<object,FilterItem|mixed>
 */
class FilterParser implements ComposerInterface
{
    protected const REGEXP = '/^\s*(?<field>[^\s\(\)]+)\s*(?:\(\s*(?<operator>[^\(\)]*?)\s*\)\s*)?$/';

    /**
     * @inheritDoc
     * @throws \LogicException
     */
    public function compose(object $accumulator, iterable $items, array $opts = []): void
    {
        throw new \LogicException(__CLASS__ . ' can only be used in combination with other query composers');
    }

    /**
     * Invoke the parser.
     *
     * @param iterable<string,mixed>|iterable<FilterItem> $filter
     * @param OptionInterface[]                           $opts
     * @return iterable<FilterItem>
     * @throws \UnexpectedValueException for unclosed parentheses in key.
     */
    public function prepare(iterable $filter, array &$opts = []): iterable
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
     * @return array{field:string,operator:string}
     * @throws \UnexpectedValueException
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

    public function apply(object $accumulator, iterable $items, array $opts): iterable
    {
        return $items;
    }

    public function finalize(object $accumulator, array $opts): void
    {
    }
}
