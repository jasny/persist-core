<?php

declare(strict_types=1);

namespace Jasny\DB\Tests\Update;

use Jasny\DB\Update as u;
use PHPUnit\Framework\TestCase;

/**
 * @coversNothing
 */
class FunctionsTest extends TestCase
{
    /**
     * @covers \Jasny\DB\Update\set
     */
    public function testSet()
    {
        $operator = u\set("foo", 42);

        $this->assertEquals('set', $operator->getOperator());
        $this->assertEquals('foo', $operator->getField());
        $this->assertEquals(42, $operator->getValue());
    }

    /**
     * @covers \Jasny\DB\Update\set
     */
    public function testSetWithArray()
    {
        $operator = u\set(["foo" => 42, 'bar' => 99]);

        $this->assertEquals('set', $operator->getOperator());
        $this->assertEquals(["foo" => 42, 'bar' => 99], $operator->getField());
    }

    /**
     * @covers \Jasny\DB\Update\patch
     */
    public function testPatch()
    {
        $operator = u\patch("foo", ['hello' => 'world']);

        $this->assertEquals('patch', $operator->getOperator());
        $this->assertEquals('foo', $operator->getField());
        $this->assertEquals(['hello' => 'world'], $operator->getValue());
    }

    /**
     * @covers \Jasny\DB\Update\inc
     */
    public function testInc()
    {
        $operator = u\inc("foo");

        $this->assertEquals('inc', $operator->getOperator());
        $this->assertEquals('foo', $operator->getField());
        $this->assertEquals(1, $operator->getValue());
    }

    /**
     * @covers \Jasny\DB\Update\inc
     */
    public function testIncValue()
    {
        $operator = u\inc("foo", 5);

        $this->assertEquals('inc', $operator->getOperator());
        $this->assertEquals('foo', $operator->getField());
        $this->assertEquals(5, $operator->getValue());
    }

    /**
     * @covers \Jasny\DB\Update\dec
     */
    public function testDec()
    {
        $operator = u\dec("foo");

        $this->assertEquals('dec', $operator->getOperator());
        $this->assertEquals('foo', $operator->getField());
        $this->assertEquals(1, $operator->getValue());
    }

    /**
     * @covers \Jasny\DB\Update\dec
     */
    public function testDecValue()
    {
        $operator = u\dec("foo", 5);

        $this->assertEquals('dec', $operator->getOperator());
        $this->assertEquals('foo', $operator->getField());
        $this->assertEquals(5, $operator->getValue());
    }

    /**
     * @covers \Jasny\DB\Update\mul
     */
    public function testMul()
    {
        $operator = u\mul("foo", 5);

        $this->assertEquals('mul', $operator->getOperator());
        $this->assertEquals('foo', $operator->getField());
        $this->assertEquals(5, $operator->getValue());
    }

    /**
     * @covers \Jasny\DB\Update\div
     */
    public function testDiv()
    {
        $operator = u\div("foo", 5);

        $this->assertEquals('div', $operator->getOperator());
        $this->assertEquals('foo', $operator->getField());
        $this->assertEquals(5, $operator->getValue());
    }

    /**
     * @covers \Jasny\DB\Update\mod
     */
    public function testMod()
    {
        $operator = u\mod("foo", 5);

        $this->assertEquals('mod', $operator->getOperator());
        $this->assertEquals('foo', $operator->getField());
        $this->assertEquals(5, $operator->getValue());
    }

    /**
     * @covers \Jasny\DB\Update\push
     */
    public function testPush()
    {
        $operator = u\push("foo", 'hello');

        $this->assertEquals('push', $operator->getOperator());
        $this->assertEquals('foo', $operator->getField());
        $this->assertEquals('hello', $operator->getValue());
    }

    /**
     * @covers \Jasny\DB\Update\pull
     */
    public function testPull()
    {
        $operator = u\pull("foo", 'hello');

        $this->assertEquals('pull', $operator->getOperator());
        $this->assertEquals('foo', $operator->getField());
        $this->assertEquals('hello', $operator->getValue());
    }
}
