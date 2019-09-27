<?php

declare(strict_types=1);

namespace Jasny\DB\Tests\Filter;

use Improved as i;
use Jasny\DB\Filter\FilterItem;
use Jasny\DB\Filter\FilterParser;
use PHPUnit\Framework\TestCase;

/**
 * @covers \Jasny\DB\Filter\FilterParser
 */
class FilterParserTest extends TestCase
{
    public function provider()
    {
        return [
            'foo'          => [['foo' => 42], new FilterItem('foo', '', 42)],
            'foo(min)'     => [['foo(min)' => 42], new FilterItem('foo', 'min', 42)],
            'foo (min)'    => [['foo (min)' => 42], new FilterItem('foo', 'min', 42)],
            ' foo ( min )' => [[' foo ( min ) ' => 42], new FilterItem('foo', 'min', 42)],
            'foo ( )'      => [['foo ( )' => 42], new FilterItem('foo', '', 42)],
            'foos'         => [['foos' => [1, 2]], new FilterItem('foos', '', [1, 2])],
        ];
    }

    /**
     * @dataProvider provider
     */
    public function testParse(array $filter, FilterItem $expected)
    {
        $parser = new FilterParser();
        $result = $parser($filter, []);

        $this->assertEquals([$expected], $result);
    }


    public function invalidParenthesesProvider()
    {
        return [
            'foo ('       => [['foo (' => 42]],
            'foo )'       => [['foo )' => 42]],
            'foo )('      => [['foo )(' => 42]],
            'foo ()('     => [['foo ()(' => 42]],
            'foo ((max))' => [['foo ((max))' => 42]],
        ];
    }

    /**
     * @dataProvider invalidParenthesesProvider
     */
    public function testInvalidParentheses(array $filter)
    {
        $this->expectException(\UnexpectedValueException::class);
        $this->expectExceptionMessage(sprintf("Invalid filter item '%s': Bad use of parentheses", key($filter)));

        $parser = new FilterParser();
        $parser($filter, []);
    }


    public function testParseFilters()
    {
        $data = $this->provider();
        $filter = array_merge(...array_column($data, 0));
        $expected = array_column($data, 1);

        $parser = new FilterParser();
        $result = $parser($filter, []);

        $this->assertEquals($expected, $result);
    }
}
