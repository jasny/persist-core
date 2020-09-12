<?php

declare(strict_types=1);

namespace Persist\Tests\Option;

use Persist\Option\Functions as opt;
use Persist\Option\FlagOption;
use Persist\Option\OptionInterface;
use PHPUnit\Framework\TestCase;

/**
 * @covers \Persist\Option\FlagOption
 */
class FlagOptionTest extends TestCase
{
    public function test()
    {
        $flag = new FlagOption('foo');
        $this->assertEquals('foo', $flag->getName());
    }

    /**
     * @covers \Persist\Option\Functions\flag
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
