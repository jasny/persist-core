<?php

declare(strict_types=1);

namespace Jasny\DB\Tests\QueryBuilder\Prepare;

use Improved as i;
use Jasny\DB\QueryBuilder\Prepare\FilterParser;
use PHPUnit\Framework\TestCase;

/**
 * @covers \Jasny\DB\QueryBuilder\Prepare\FilterParser
 */
class FilterParserTest extends TestCase
{
    public function provider()
    {
        return [
            'foo'          => [['foo' => 42], 'foo', '', 42],
            'foo(min)'     => [['foo(min)' => 42], 'foo', 'min', 42],
            'foo (min)'    => [['foo (min)' => 42], 'foo', 'min', 42],
            ' foo ( min )' => [[' foo ( min ) ' => 42], 'foo', 'min', 42],
            'foo ( )'      => [['foo ( )' => 42], 'foo', '', 42],
            'foos'         => [['foos' => [1, 2]], 'foos', '', [1, 2]],
        ];
    }

    /**
     * @dataProvider provider
     */
    public function testParse(array $filter, string $field, string $operator, $value)
    {
        $parser = new FilterParser();
        $iterator = $parser($filter, []);
        $result = [];

        foreach ($iterator as $info => $value) {
            $this->assertIsArray($info);
            $result[] = $info + compact('value');
        }

        $expected = compact('field', 'operator', 'value');
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
        $iterator = $parser($filter, []);

        i\iterable_walk($iterator);
    }
}
