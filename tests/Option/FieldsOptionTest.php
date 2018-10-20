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
        $option = new FieldsOption('foo', 'bar', 'color.red');
        $this->assertEquals(['foo', 'bar', 'color.red'], $option->getFields());
    }

    /**
     * @covers \Jasny\DB\Option\fields
     */
    public function testFunction()
    {
        $option = opt\fields('foo', 'bar', 'color.red');
        $this->assertEquals(['foo', 'bar', 'color.red'], $option->getFields());
    }
}
