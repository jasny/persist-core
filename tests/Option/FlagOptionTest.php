<?php

declare(strict_types=1);

namespace Jasny\DB\Tests\Option;

use Jasny\DB\Option\FlagOption;
use PHPUnit\Framework\TestCase;
use function Jasny\DB\Option\preserve_keys;

/**
 * @covers \Jasny\DB\Option\FlagOption
 */
class FlagOptionTest extends TestCase
{
    public function test()
    {
        $flag = new FlagOption('foo');
        $this->assertEquals('foo', $flag->getType());
    }

    /**
     * @covers \Jasny\DB\Option\preserve_keys
     */
    public function testPreserveKeysFunction()
    {
        $flag = preserve_keys();

        $this->assertInstanceOf(FlagOption::class, $flag);
        $this->assertEquals('preserve_keys', $flag->getType());
    }
}
