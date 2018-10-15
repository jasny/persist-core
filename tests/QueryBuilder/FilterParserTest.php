<?php

namespace Jasny\DB\Tests\QueryBuilder;

use Improved as i;
use Jasny\DB\QueryBuilder\FilterParser;
use PHPUnit\Framework\TestCase;

/**
 * @covers \Jasny\DB\QueryBuilder\FilterParser
 */
class FilterParserTest extends TestCase
{
    public function provider()
    {
        return [
            [['foo' => 42], 'foo', '', 42],
            [['foo(min)' => 42], 'foo', 'min', 42],
            [['foo (min)' => 42], 'foo', 'min', 42],
            [[' foo ( min ) ' => 42], 'foo', 'min', 42],
            [['foo ( )' => 42], 'foo', '', 42],
            [['foo-bar' => [1, 2]], 'foo-bar', '', [1, 2]],
        ];
    }

    /**
     * @dataProvider provider
     */
    public function test(array $filter, string $field, string $operator, $value)
    {
        $parser = new FilterParser();
        $iterator = $parser($filter);
        $result = [];

        foreach ($iterator as $info => $value) {
            $result[] = $info + compact('value');
        }

        $expected = compact('field', 'operator', 'value');
        $this->assertEquals([$expected], $result);
    }


    public function invalidParenthesesProvider()
    {
        return [
            [['foo (' => 42]],
            [['foo )' => 42]],
            [['foo )(' => 42]],
            [['foo ()(' => 42]],
            [['foo ((max))' => 42]],
        ];
    }

    /**
     * @dataProvider invalidParenthesesProvider
     * @expectedException \Jasny\DB\Exception\InvalidFilterException
     */
    public function testInvalidParentheses(array $filter)
    {
        $this->expectExceptionMessage(sprintf("Invalid filter item '%s': Bad use of parentheses", key($filter)));

        $parser = new FilterParser();
        $iterator = $parser($filter);

        i\iterable_walk($iterator);
    }
}
