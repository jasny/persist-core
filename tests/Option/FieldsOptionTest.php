<?php

declare(strict_types=1);

namespace Jasny\DB\Tests\Option;

use Jasny\DB\Option as opt;
use Jasny\DB\Option\FieldsOption;
use PHPUnit\Framework\TestCase;

/**
 * @covers \Jasny\DB\Option\FieldsOption
 */
class FieldsOptionTest extends TestCase
{
    public function testBasic()
    {
        $option = new FieldsOption(['foo', 'bar', 'color.red']);

        $this->assertEquals(['foo', 'bar', 'color.red'], $option->getFields());
        $this->assertFalse($option->isNegated());
    }

    public function testNegated()
    {
        $option = new FieldsOption(['foo', 'bar', 'color.red'], true);

        $this->assertEquals(['foo', 'bar', 'color.red'], $option->getFields());
        $this->assertTrue($option->isNegated());
    }

    /**
     * @covers \Jasny\DB\Option\fields
     */
    public function testFieldsFunction()
    {
        $option = opt\fields('foo', 'bar', 'color.red');

        $this->assertInstanceOf(FieldsOption::class, $option);
        $this->assertEquals(['foo', 'bar', 'color.red'], $option->getFields());
        $this->assertFalse($option->isNegated());
    }

    /**
     * @covers \Jasny\DB\Option\omit
     */
    public function testOmitFunction()
    {
        $option = opt\omit('foo', 'bar', 'color.red');

        $this->assertInstanceOf(FieldsOption::class, $option);
        $this->assertEquals(['foo', 'bar', 'color.red'], $option->getFields());
        $this->assertTrue($option->isNegated());
    }
}
