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

    public function testGetMeta()
    {
        $input = (object)['total' => 42];
        $result = new Result([], $input);

        $meta = $result->getMeta();

        $this->assertEquals($input, $meta);
        $this->assertNotSame($input, $meta);
    }

    public function testGetMetaWithArray()
    {
        $result = new Result([], ['total' => 42]);

        $this->assertEquals((object)['total' => 42], $result->getMeta());
    }

    public function testGetMetaImmutable()
    {
        $input = (object)['total' => 42];
        $result = new Result([], $input);

        $meta = $result->getMeta();
        $meta->foo = 'bar';

        $this->assertEquals((object)['total' => 42], $result->getMeta());
    }

    public function testGetMetaNotSet()
    {
        $result = new Result([]);

        $this->assertEquals((object)[], $result->getMeta());
    }

    public function testGetMetaWithClosure()
    {
        $closure = $this->createCallbackMock($this->once(), [], ['total' => 42]);

        $result = new Result([], $closure);

        $this->assertEquals((object)['total' => 42], $result->getMeta());

        // Closure should not be called twice
        $this->assertEquals((object)['total' => 42], $result->getMeta());
    }

    /**
     * @expectedException \UnexpectedValueException
     * @expectedExceptionMessage Failed to get total count: Expected stdClass object or array, got string
     */
    public function testGetMetaWithClosureReturnsString()
    {
        $closure = $this->createCallbackMock($this->once(), [], 'foo');

        $result = new Result([], $closure);
        $result->getMeta();
    }
}
