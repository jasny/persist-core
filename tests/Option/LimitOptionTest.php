<?php

declare(strict_types=1);

namespace Persist\Tests\Option;

use Persist\Option\Functions as opt;
use Persist\Option\LimitOption;
use PHPUnit\Framework\TestCase;

/**
 * @covers \Persist\Option\LimitOption
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
     * @covers \Persist\Option\Functions\limit
     */
    public function testLimitFunction()
    {
        $option = opt\limit(10);

        $this->assertInstanceOf(LimitOption::class, $option);
        $this->assertEquals(10, $option->getLimit());
        $this->assertEquals(0, $option->getOffset());
    }

    /**
     * @covers \Persist\Option\Functions\limit
     */
    public function testLimitFunctionOffset()
    {
        $option = opt\limit(10, 40);

        $this->assertInstanceOf(LimitOption::class, $option);
        $this->assertEquals(10, $option->getLimit());
        $this->assertEquals(40, $option->getOffset());
    }

    /**
     * @covers \Persist\Option\Functions\page
     */
    public function testPageFunction()
    {
        $option = opt\page(5, 10);

        $this->assertInstanceOf(LimitOption::class, $option);
        $this->assertEquals(10, $option->getLimit());
        $this->assertEquals(40, $option->getOffset());
    }
}
