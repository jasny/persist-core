<?php

namespace Jasny\DB\Tests\Result;

use Jasny\DB\Result\Result;
use Jasny\TestHelper;
use PHPUnit\Framework\TestCase;

/**
 * @covers \Jasny\DB\Result\Result
 */
class ResultTest extends TestCase
{
    use TestHelper;

    /**
     * Test 'getTotalCount' method
     */
    public function testGetTotalCount()
    {
        $result = new Result([], 42);

        $this->assertSame(42, $result->getTotalCount());
    }

    /**
     * Test 'getTotalCount' method with closure
     */
    public function testGetTotalCountWithClosure()
    {
        $closure = $this->createCallbackMock($this->once(), [], 21);

        $result = new Result([], $closure);

        $this->assertSame(21, $result->getTotalCount());

        // Closure should not be called twice
        $this->assertSame(21, $result->getTotalCount());
    }

    /**
     * Test 'getTotalCount' method, in case when totalCount proerpty is not set
     *
     * @expectedException \BadMethodCallException
     */
    public function testGetTotalCountNotSet()
    {
        $result = new Result([]);
        $result->getTotalCount();
    }

    /**
     * Test 'getTotalCount' method with closure that returns a negative number
     *
     * @expectedException \UnexpectedValueException
     * @expectedExceptionMessage Failed to get total count: Expected a positive integer, got -1
     */
    public function testGetTotalCountWithClosureNegative()
    {
        $closure = $this->createCallbackMock($this->once(), [], -1);

        $result = new Result([], $closure);
        $result->getTotalCount();
    }

    /**
     * Test 'getTotalCount' method with closure that returns a string
     *
     * @expectedException \UnexpectedValueException
     * @expectedExceptionMessage Failed to get total count: Expected a positive integer, got string
     */
    public function testGetTotalCountWithClosureString()
    {
        $closure = $this->createCallbackMock($this->once(), [], 'foo');

        $result = new Result([], $closure);
        $result->getTotalCount();
    }
}
