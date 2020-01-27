<?php

declare(strict_types=1);

namespace Jasny\DB\Tests\Option;

use Jasny\DB\Option as opts;
use Jasny\DB\Option\FlagOption;
use Jasny\DB\Option\OptionInterface;
use PHPUnit\Framework\TestCase;

/**
 * @covers \Jasny\DB\Option\FlagOption
 */
class FlagOptionTest extends TestCase
{
    public function test()
    {
        $flag = new FlagOption('foo');
        $this->assertEquals('foo', $flag->getName());
    }

    public function testFunction()
    {
        $flag = opts\flag('foo');
        $this->assertEquals('foo', $flag->getName());
    }

    public function testIsIn()
    {
        $opts = [
            $this->createMock(OptionInterface::class),
            new FlagOption('foo'),
            $this->createMock(OptionInterface::class),
            new FlagOption('foo.bar'),
            $this->createMock(OptionInterface::class),
        ];

        $this->assertTrue((new FlagOption('foo.bar'))->isIn($opts));
        $this->assertFalse((new FlagOption('zoo'))->isIn($opts));
    }

    /**
     * @covers \Jasny\DB\Option\apply_result
     */
    public function testApplyResultFunction()
    {
        $flag = opts\apply_result();

        $this->assertInstanceOf(FlagOption::class, $flag);
        $this->assertEquals('apply_result', $flag->getName());
    }
}
