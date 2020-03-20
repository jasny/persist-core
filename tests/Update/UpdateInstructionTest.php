<?php

declare(strict_types=1);

namespace Jasny\DB\Tests\Update;

use Jasny\DB\Update\Functions as update;
use Jasny\DB\Update\UpdateInstruction;
use PHPUnit\Framework\TestCase;

/**
 * @covers \Jasny\DB\Update\UpdateInstruction
 */
class UpdateInstructionTest extends TestCase
{
    public function testBasic()
    {
        $operation = new UpdateInstruction('set', ['foo' => 42]);

        $this->assertEquals('set', $operation->getOperator());
        $this->assertEquals(['foo' => 42], $operation->getPairs());
    }

    public function testWithMultiplePairs()
    {
        $operation = new UpdateInstruction('set', ['foo' => 42, 'bar' => 99]);

        $this->assertEquals('set', $operation->getOperator());
        $this->assertEquals(['foo' => 42, 'bar' => 99], $operation->getPairs());
    }

    public function testCustomOperator()
    {
        $operation = new UpdateInstruction('custom', ['foo' => 42, 'bar' => 99]);

        $this->assertEquals('custom', $operation->getOperator());
        $this->assertEquals(['foo' => 42, 'bar' => 99], $operation->getPairs());
    }

    /**
     * @covers \Jasny\DB\Update\Functions\set
     */
    public function testSet()
    {
        $operator = update\set("foo", 42);

        $this->assertEquals('set', $operator->getOperator());
        $this->assertEquals(['foo' => 42], $operator->getPairs());
    }

    /**
     * @covers \Jasny\DB\Update\Functions\clear
     */
    public function testClear()
    {
        $operator = update\clear("foo", "bar");

        $this->assertEquals('clear', $operator->getOperator());
        $this->assertEquals(['foo' => null, 'bar' => null], $operator->getPairs());
    }

    /**
     * @covers \Jasny\DB\Update\Functions\set
     */
    public function testSetWithArray()
    {
        $operator = update\set(["foo" => 42, 'bar' => 99]);

        $this->assertEquals('set', $operator->getOperator());
        $this->assertEquals(["foo" => 42, 'bar' => 99], $operator->getPairs());
    }

    /**
     * @covers \Jasny\DB\Update\Functions\patch
     */
    public function testPatch()
    {
        $operator = update\patch("foo", ['hello' => 'world']);

        $this->assertEquals('patch', $operator->getOperator());
        $this->assertEquals(['foo' => ['hello' => 'world']], $operator->getPairs());
    }

    public function numberProvider()
    {
        return [
            '-1 (int)'     => [-1],
            '0 (int)'      => [0],
            '1 (int)'      => [1],
            '2.14 (float)' => [2.14],
        ];
    }

    /**
     * @covers \Jasny\DB\Update\Functions\inc
     */
    public function testInc()
    {
        $operator = update\inc("foo");

        $this->assertEquals('inc', $operator->getOperator());
        $this->assertEquals(['foo' => 1], $operator->getPairs());
    }

    /**
     * @dataProvider numberProvider
     * @covers \Jasny\DB\Update\Functions\inc
     */
    public function testIncWithAValue($number)
    {
        $operator = update\inc("foo", $number);

        $this->assertEquals('inc', $operator->getOperator());
        $this->assertEquals(['foo' => $number], $operator->getPairs());
    }

    /**
     * @covers \Jasny\DB\Update\Functions\dec
     */
    public function testDec()
    {
        $operator = update\dec("foo");

        $this->assertEquals('inc', $operator->getOperator());
        $this->assertEquals(['foo' => -1], $operator->getPairs());
    }

    /**
     * @dataProvider numberProvider
     * @covers \Jasny\DB\Update\Functions\dec
     */
    public function testDecWithAValue($number)
    {
        $operator = update\dec("foo", $number);

        $this->assertEquals('inc', $operator->getOperator());
        $this->assertEquals(['foo' => -1 * $number], $operator->getPairs());
    }

    /**
     * @dataProvider numberProvider
     * @covers \Jasny\DB\Update\Functions\mul
     */
    public function testMul($number)
    {
        $operator = update\mul("foo", $number);

        $this->assertEquals('mul', $operator->getOperator());
        $this->assertEquals(['foo' => $number], $operator->getPairs());
    }

    /**
     * @dataProvider numberProvider
     * @covers \Jasny\DB\Update\Functions\div
     */
    public function testDiv($number)
    {
        $operator = update\div("foo", $number);

        $this->assertEquals('div', $operator->getOperator());
        $this->assertEquals(['foo' => $number], $operator->getPairs());
    }

    /**
     * @dataProvider numberProvider
     * @covers \Jasny\DB\Update\Functions\mod
     */
    public function testMod($number)
    {
        $operator = update\mod("foo", $number);

        $this->assertEquals('mod', $operator->getOperator());
        $this->assertEquals(['foo' => $number], $operator->getPairs());
    }

    /**
     * @covers \Jasny\DB\Update\Functions\push
     */
    public function testPush()
    {
        $operator = update\push("foo", 'hello');

        $this->assertEquals('push', $operator->getOperator());
        $this->assertEquals(['foo' => ['hello']], $operator->getPairs());
    }

    /**
     * @covers \Jasny\DB\Update\Functions\push
     */
    public function testPushMultiple()
    {
        $operator = update\push("foo", 'hello', 'sweet', 'world');

        $this->assertEquals('push', $operator->getOperator());
        $this->assertEquals(['foo' => ['hello', 'sweet', 'world']], $operator->getPairs());
    }

    /**
     * @covers \Jasny\DB\Update\Functions\pull
     */
    public function testPull()
    {
        $operator = update\pull("foo", 'hello');

        $this->assertEquals('pull', $operator->getOperator());
        $this->assertEquals(['foo' => ['hello']], $operator->getPairs());
    }

    /**
     * @covers \Jasny\DB\Update\Functions\pull
     */
    public function testPullMultiple()
    {
        $operator = update\pull("foo", 'hello', 'sweet', 'world');

        $this->assertEquals('pull', $operator->getOperator());
        $this->assertEquals(['foo' => ['hello', 'sweet', 'world']], $operator->getPairs());
    }
}
