<?php

namespace Jasny\DB\Tests\Option;

use Jasny\DB\Option as opt;
use Jasny\DB\Option\FieldsOption;
use PHPUnit\Framework\TestCase;

/**
 * @covers \Jasny\DB\Option\FieldsOption
 */
class FieldsOptionTest extends TestCase
{
    public function test()
    {
        $option = new FieldsOption('do', ['foo', 'bar', 'color.red']);

        $this->assertEquals('do', $option->getType());
        $this->assertEquals(['foo', 'bar', 'color.red'], $option->getFields());
    }


    /**
     * @covers \Jasny\DB\Option\fields
     */
    public function testFieldsFunction()
    {
        $option = opt\fields('foo', 'bar', 'color.red');

        $this->assertInstanceOf(FieldsOption::class, $option);
        $this->assertEquals('fields', $option->getType());
        $this->assertEquals(['foo', 'bar', 'color.red'], $option->getFields());
    }

    /**
     * @covers \Jasny\DB\Option\omit
     */
    public function testOmitFunction()
    {
        $option = opt\omit('foo', 'bar', 'color.red');

        $this->assertInstanceOf(FieldsOption::class, $option);
        $this->assertEquals('omit', $option->getType());
        $this->assertEquals(['foo', 'bar', 'color.red'], $option->getFields());
    }

    /**
     * @covers \Jasny\DB\Option\sort
     */
    public function testSortFunction()
    {
        $option = opt\sort('foo', '~bar', 'color.red');

        $this->assertInstanceOf(FieldsOption::class, $option);
        $this->assertEquals('sort', $option->getType());
        $this->assertEquals(['foo', '~bar', 'color.red'], $option->getFields());
    }
}
