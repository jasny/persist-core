<?php

declare(strict_types=1);

namespace Jasny\DB\Tests\QueryBuilder\Build;

use Improved\Iterator\CombineIterator;
use Jasny\DB\Option\OptionInterface;
use Jasny\DB\QueryBuilder\Build\BuildQuery;
use Jasny\TestHelper;
use PHPUnit\Framework\TestCase;

/**
 * @covers \Jasny\DB\QueryBuilder\Build\BuildQuery
 */
class BuildQueryTest extends TestCase
{
    use TestHelper;

    public function test()
    {
        $accumulator = (object)[];
        $opts = [
            $this->createMock(OptionInterface::class),
            $this->createMock(OptionInterface::class),
        ];

        $composeInfo = [
            ['field' => 'foo', 'value' => 42],
            ['field' => 'bar', 'operator' => 'min', 'value' => 10],
            ['field' => 'zoo', 'operator' => 'exists'],
        ];
        $composeCallbacks = [
            $this->createCallbackMock($this->once(), [$this->identicalTo($accumulator), 'foo', '', 42, $opts]),
            $this->createCallbackMock($this->once(), [$this->identicalTo($accumulator), 'bar', 'min', 10, $opts]),
            $this->createCallbackMock($this->once(), [$this->identicalTo($accumulator), 'zoo', 'exists', null, $opts]),
        ];
        $compose = new CombineIterator($composeInfo, $composeCallbacks);

        $build = new BuildQuery();
        $build($accumulator, $compose, $opts);
    }
}
