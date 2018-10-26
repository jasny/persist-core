<?php declare(strict_types=1);

namespace Jasny\DB\Tests\QueryBuilder;

use Jasny\DB\QueryBuilder\CustomFilter;
use PHPUnit\Framework\TestCase;

/**
 * @covers \Jasny\DB\QueryBuilder\CustomFilter
 */
class CustomFilterTest extends TestCase
{
    public function test()
    {
        $fn = function() {};

        $filter = new CustomFilter('foo', $fn);

        $iterate = function(array $keys) {
            foreach ($keys as $key) {
                yield $key => null;
            }
        };

        $iterator = $iterate([['field' => 'foo'], ['wuz'], 'foo', ['field' => 'bar']]);

        $filtered = $filter($iterator);
        $result = iterator_to_array($filtered, false);

        $this->assertSame([$fn, null, $fn, null], $result);
    }
}
