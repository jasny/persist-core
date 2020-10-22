<?php

declare(strict_types=1);

namespace Jasny\Tests\Persist\Option;

use Jasny\Persist\Option\Functions as opt;
use Jasny\Persist\Option\FieldsOption;
use PHPUnit\Framework\TestCase;

/**
 * @covers \Jasny\Persist\Option\FieldsOption
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
     * @covers \Jasny\Persist\Option\Functions\fields
     */
    public function testFieldsFunction()
    {
        $option = opt\fields('foo', 'bar', 'color.red');

        $this->assertInstanceOf(FieldsOption::class, $option);
        $this->assertEquals(['foo', 'bar', 'color.red'], $option->getFields());
        $this->assertFalse($option->isNegated());
    }

    /**
     * @covers \Jasny\Persist\Option\Functions\omit
     */
    public function testOmitFunction()
    {
        $option = opt\omit('foo', 'bar', 'color.red');

        $this->assertInstanceOf(FieldsOption::class, $option);
        $this->assertEquals(['foo', 'bar', 'color.red'], $option->getFields());
        $this->assertTrue($option->isNegated());
    }
}
