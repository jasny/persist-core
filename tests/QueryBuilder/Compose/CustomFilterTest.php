<?php

declare(strict_types=1);

namespace Jasny\DB\Tests\QueryBuilder\Compose;

use Improved as i;
use Jasny\DB\QueryBuilder\Compose\CustomFilter;
use Jasny\TestHelper;
use PHPUnit\Framework\MockObject\Builder\InvocationMocker;
use PHPUnit\Framework\TestCase;

/**
 * @covers \Jasny\DB\QueryBuilder\Compose\CustomFilter
 */
class CustomFilterTest extends TestCase
{
    use TestHelper;

    public function testWithField()
    {
        $function = fn() => null;
        $orig = [fn() => 0, fn() => 0, null, null];

        $filter = new CustomFilter('foo', $function);

        $iterate = static function (...$keys) use ($orig): \Generator {
            foreach ($keys as $i => $key) {
                yield ($key) => $orig[$i];
            }
        };

        $iterator = $iterate(['field' => 'foo'], ['wuz'], 'foo', ['field' => 'bar (any)']);

        $filtered = $filter($iterator);
        $this->assertInstanceOf(\Traversable::class, $iterator);

        $result = i\iterable_to_array($filtered, false);

        $this->assertSame([$function, $orig[1], $function, null], $result);
    }

    public function testWithCondition()
    {
        $condition = $this->createCallbackMock($this->exactly(3), function (InvocationMocker $invoke) {
            $invoke->withConsecutive(
                ['foo', 'not'],
                ['foo', ''],
                ['bar', 'any']
            )->willReturnOnConsecutiveCalls(true, true, false);
        });

        $function = fn() => null;
        $orig = [fn() => 0, fn() => 0, null, null];

        $filter = new CustomFilter(\Closure::fromCallable($condition), $function);

        $iterate = static function (...$keys) use ($orig): \Generator {
            foreach ($keys as $i => $key) {
                yield ($key) => $orig[$i];
            }
        };

        $iterator = $iterate(
            ['field' => 'foo', 'operator' => 'not'],
            ['wuz'],
            'foo',
            ['field' => 'bar', 'operator' => 'any']
        );

        $filtered = $filter($iterator);
        $this->assertInstanceOf(\Traversable::class, $iterator);

        $result = i\iterable_to_array($filtered, false);

        $this->assertSame([$function, $orig[1], $function, null], $result);
    }
}
