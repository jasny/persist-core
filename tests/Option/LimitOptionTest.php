<?php

declare(strict_types=1);

namespace Jasny\DB\Tests\Option;

use Jasny\DB\Option\Functions as opt;
use Jasny\DB\Option\LimitOption;
use PHPUnit\Framework\TestCase;

/**
 * @covers \Jasny\DB\Option\LimitOption
 */
class LimitOptionTest extends TestCase
{
    public function test()
    {
        $option = new LimitOption(10);

        $this->assertEquals(10, $option->getLimit());
        $this->assertEquals(0, $option->getOffset());
    }

    public function testOffset()
    {
        $option = new LimitOption(10, 40);

        $this->assertEquals(10, $option->getLimit());
        $this->assertEquals(40, $option->getOffset());
    }

    /**
     * @covers \Jasny\DB\Option\Functions\limit
     */
    public function testLimitFunction()
    {
        $option = opt\limit(10);

        $this->assertInstanceOf(LimitOption::class, $option);
        $this->assertEquals(10, $option->getLimit());
        $this->assertEquals(0, $option->getOffset());
    }

    /**
     * @covers \Jasny\DB\Option\Functions\limit
     */
    public function testLimitFunctionOffset()
    {
        $option = opt\limit(10, 40);

        $this->assertInstanceOf(LimitOption::class, $option);
        $this->assertEquals(10, $option->getLimit());
        $this->assertEquals(40, $option->getOffset());
    }

    /**
     * @covers \Jasny\DB\Option\Functions\page
     */
    public function testPageFunction()
    {
        $option = opt\page(5, 10);

        $this->assertInstanceOf(LimitOption::class, $option);
        $this->assertEquals(10, $option->getLimit());
        $this->assertEquals(40, $option->getOffset());
    }
}
