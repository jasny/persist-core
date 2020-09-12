<?php

declare(strict_types=1);

namespace Persist\Tests\Option;

use Persist\Option\Functions as opt;
use Persist\Option\SortOption;
use PHPUnit\Framework\TestCase;

/**
 * @covers \Persist\Option\SortOption
 */
class SortOptionTest extends TestCase
{
    public function testBasic()
    {
        $option = new SortOption(['foo', '~bar', 'color.red']);

        $this->assertEquals(['foo', '~bar', 'color.red'], $option->getFields());
    }

    /**
     * @covers \Persist\Option\Functions\sort
     */
    public function testSortFunction()
    {
        $option = opt\sort('foo', '~bar', 'color.red');

        $this->assertInstanceOf(SortOption::class, $option);
        $this->assertEquals(['foo', '~bar', 'color.red'], $option->getFields());
    }
}
