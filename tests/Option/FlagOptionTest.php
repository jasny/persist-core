<?php

declare(strict_types=1);

namespace Jasny\Persist\Tests\Option;

use Jasny\Persist\Option\Functions as opt;
use Jasny\Persist\Option\FlagOption;
use Jasny\Persist\Option\OptionInterface;
use PHPUnit\Framework\TestCase;

/**
 * @covers \Jasny\Persist\Option\FlagOption
 */
class FlagOptionTest extends TestCase
{
    public function test()
    {
        $flag = new FlagOption('foo');
        $this->assertEquals('foo', $flag->getName());
    }

    /**
     * @covers \Jasny\Persist\Option\Functions\flag
     */
    public function testFunction()
    {
        $flag = opt\flag('foo');
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
}
