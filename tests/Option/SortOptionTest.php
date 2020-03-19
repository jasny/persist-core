<?php

declare(strict_types=1);

namespace Jasny\DB\Tests\Option;

use Jasny\DB\Option\Functions as opt;
use Jasny\DB\Option\SortOption;
use PHPUnit\Framework\TestCase;

/**
 * @covers \Jasny\DB\Option\SortOption
 */
class SortOptionTest extends TestCase
{
    public function testBasic()
    {
        $option = new SortOption(['foo', '~bar', 'color.red']);

        $this->assertEquals(['foo', '~bar', 'color.red'], $option->getFields());
    }

    /**
     * @covers \Jasny\DB\Option\Functions\sort
     */
    public function testSortFunction()
    {
        $option = opt\sort('foo', '~bar', 'color.red');

        $this->assertInstanceOf(SortOption::class, $option);
        $this->assertEquals(['foo', '~bar', 'color.red'], $option->getFields());
    }
}
