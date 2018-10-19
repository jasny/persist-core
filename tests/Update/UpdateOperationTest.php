<?php

namespace Jasny\DB\Tests\Update;

use Jasny\DB\Update\UpdateOperation;
use PHPUnit\Framework\TestCase;

/**
 * @covers \Jasny\DB\Update\UpdateOperation
 */
class UpdateOperationTest extends TestCase
{
    public function test()
    {
        $operation = new UpdateOperation('answer', 'foo', 42);

        $this->assertEquals('answer', $operation->getOperator());
        $this->assertEquals('foo', $operation->getField());
        $this->assertEquals(42, $operation->getValue());
    }

    public function testWithArray()
    {
        $operation = new UpdateOperation('answer', ['foo' => 42, 'bar' => 99], null);

        $this->assertEquals('answer', $operation->getOperator());
        $this->assertEquals(['foo' => 42, 'bar' => 99], $operation->getField());
        $this->assertNull($operation->getValue());
    }
}
