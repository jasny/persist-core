<?php

namespace Jasny\DB\Tests\Option;

use Jasny\DB\Option as opt;
use Jasny\DB\Option\SortOption;
use PHPUnit\Framework\TestCase;

/**
 * @covers \Jasny\DB\Option\SortOption
 */
class SortOptionTest extends TestCase
{
    public function test()
    {
        $option = new SortOption('foo', '~bar', 'color.red');
        $this->assertEquals(['foo', '~bar', 'color.red'], $option->getFields());
    }

    /**
     * @covers \Jasny\DB\Option\sort
     */
    public function testFunction()
    {
        $option = opt\sort('foo', '~bar', 'color.red');
        $this->assertEquals(['foo', '~bar', 'color.red'], $option->getFields());
    }
}
