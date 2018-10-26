<?php declare(strict_types=1);

namespace Jasny\DB\Tests\Result;

use Improved\IteratorPipeline\Pipeline;
use Jasny\DB\Result\Result;
use Jasny\TestHelper;
use PHPUnit\Framework\TestCase;

/**
 * @covers \Jasny\DB\Result\Result
 */
class ResultTest extends TestCase
{
    use TestHelper;

    public function testPipeline()
    {
        $result = new Result([]);
        $this->assertInstanceOf(Pipeline::class, $result);
    }

    public function testGetMeta()
    {
        $result = new Result([], ['total' => 42]);

        $this->assertEquals(['total' => 42], $result->getMeta());
    }

    public function testGetMetaNotSet()
    {
        $result = new Result([]);

        $this->assertEquals([], $result->getMeta());
    }

    public function testGetMetaWithClosure()
    {
        $closure = $this->createCallbackMock($this->once(), [], ['total' => 42]);

        $result = new Result([], $closure);

        $this->assertEquals(['total' => 42], $result->getMeta());

        // Closure should not be called twice
        $this->assertEquals(['total' => 42], $result->getMeta());
    }

    /**
     * @expectedException \UnexpectedValueException
     * @expectedExceptionMessage Failed to get meta: Expected array, got string
     */
    public function testGetMetaWithClosureReturnsString()
    {
        $closure = $this->createCallbackMock($this->once(), [], 'foo');

        $result = new Result([], $closure);
        $result->getMeta();
    }

    public function testWithMeta()
    {
        $base = new Result([]);

        $result = $base->withMeta(['total' => 42]);

        $this->assertNotSame($base, $result);
        $this->assertEquals(['total' => 42], $result->getMeta());
    }
}
