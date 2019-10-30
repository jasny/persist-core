<?php

declare(strict_types=1);

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

    public function testGetMetaUsingKey()
    {
        $result = new Result([], ['total' => 42]);

        $this->assertEquals(42, $result->getMeta('total'));
        $this->assertNull($result->getMeta('other'));
    }
}
