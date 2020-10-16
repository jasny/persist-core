<?php

declare(strict_types=1);

namespace Persist\Query;

use Persist\Filter\FilterItem;
use Persist\Option\OptionInterface;
use Spatie\Regex\Regex;
use Spatie\Regex\RegexFailed;

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
     * @param object            $accumulator
     * @param iterable          $filter
     * @param OptionInterface[] $opts
     * @return iterable
     *
     * @phpstan-param TQuery&object     $accumulator
     * @phpstan-param iterable<mixed>   $filter
     * @phpstan-param OptionInterface[] $opts
     * @phpstan-return iterable<FilterItem>
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
     * @return array{field:string,operator:string}
     * @throws \UnexpectedValueException
     */
    protected function parse(string $key): array
    {
        // Quick return for field without an operator
        if (strpos($key, '(') === false && strpos($key, ')') === false) {
            return ['field' => trim($key), 'operator' => ''];
        }

        // Use regex to parse field and operator
        try {
            $result = Regex::match(static::REGEXP, $key);

            if (!$result->hasMatch()) {
                throw new \UnexpectedValueException("Invalid filter item '$key': Bad use of parentheses");
            }

            return [
                'field' => $result->group('field'),
                'operator' => $result->groupOr('operator', ''),
            ];
        } catch (RegexFailed $exception) {
            throw new \UnexpectedValueException("Invalid filter item '$key'", 0, $exception);
        }
    }
}
