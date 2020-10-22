<?php

declare(strict_types=1);

namespace Jasny\Tests\Persist\Option;

use Jasny\Persist\Option\Functions as opt;
use Jasny\Persist\Option\SortOption;
use PHPUnit\Framework\TestCase;

/**
 * @covers \Jasny\Persist\Option\SortOption
 */
class SortOptionTest extends TestCase
{
    public function testBasic()
    {
        $option = new SortOption(['foo', '~bar', 'color.red']);

        $this->assertEquals(['foo', '~bar', 'color.red'], $option->getFields());
    }

    /**
     * @covers \Jasny\Persist\Option\Functions\sort
     */
    public function testSortFunction()
    {
        $option = opt\sort('foo', '~bar', 'color.red');

        $this->assertInstanceOf(SortOption::class, $option);
        $this->assertEquals(['foo', '~bar', 'color.red'], $option->getFields());
    }
}
