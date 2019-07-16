<?php

declare(strict_types=1);

namespace Jasny\DB\Tests\QueryBuilder\Step;

use Improved as i;
use Jasny\DB\QueryBuilder\Step\CustomFilter;
use PHPUnit\Framework\TestCase;

/**
 * @covers \Jasny\DB\QueryBuilder\Step\CustomFilter
 */
class CustomFilterTest extends TestCase
{
    public function test()
    {
        $function = function () {
        };

        $filter = new CustomFilter('foo', $function);

        $iterate = function (array $keys) {
            foreach ($keys as $key) {
                yield ($key) => null;
            }
        };

        $iterator = $iterate([['field' => 'foo'], ['wuz'], 'foo', ['field' => 'bar']]);

        $filtered = $filter($iterator);
        $this->assertInstanceOf(\Traversable::class, $iterator);

        $result = i\iterable_to_array($filtered, false);

        $this->assertSame([$function, null, $function, null], $result);
    }
}
