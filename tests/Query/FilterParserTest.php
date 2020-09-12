<?php

declare(strict_types=1);

namespace Persist\Tests\Query;

use Improved as i;
use Persist\Filter\FilterItem;
use Persist\Query\FilterParser;
use PHPUnit\Framework\TestCase;

/**
 * @covers \Persist\Query\FilterParser
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
        $iterator = $parser->prepare($filter);
        $result = i\iterable_to_array($iterator);

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
        $parser = new FilterParser();
        $iterator = $parser->prepare($filter);

        $this->expectException(\UnexpectedValueException::class);
        $this->expectExceptionMessage(sprintf("Invalid filter item '%s': Bad use of parentheses", key($filter)));

        i\iterable_walk($iterator);
    }

    public function testParseFilters()
    {
        $data = $this->provider();
        $filter = array_merge(...array_column($data, 0));
        $expected = array_column($data, 1);

        $parser = new FilterParser();
        $iterator = $parser->prepare($filter);
        $result = i\iterable_to_array($iterator);

        $this->assertEquals($expected, $result);
    }

    public function testSkipFilterItem()
    {
        $filter = [
            new FilterItem('a', '', null),
            new FilterItem('b', '', null),
            'foo' => 42,
        ];

        $parser = new FilterParser();
        $iterator = $parser->prepare($filter);
        $result = i\iterable_to_array($iterator);

        $this->assertIsArray($result);
        $this->assertCount(3, $result);

        $this->assertSame($filter[0], $result[0]);
        $this->assertSame($filter[1], $result[1]);
        $this->assertEquals(new FilterItem('foo', '', 42), $result[2]);
    }
}
