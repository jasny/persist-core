<?php

declare(strict_types=1);

namespace Jasny\DB\Tests\QueryBuilder\Prepare;

use Improved as i;
use Jasny\DB\Option\FlagOption;
use Jasny\DB\Option\OptionInterface;
use Jasny\DB\QueryBuilder\Parser\ItemAsKey;
use PHPUnit\Framework\TestCase;

/**
 * @covers \Jasny\DB\QueryBuilder\Parser\ItemAsKey
 */
class ItemAsKeyTest extends TestCase
{
    public function test()
    {
        $values = ['one' => 'uno', 'two' => 'dos', 'three' => 'tres'];
        $opts = [
            new FlagOption('foo'),
            $this->createMock(OptionInterface::class)
        ];

        $step = new ItemAsKey();
        $result = $step($values, $opts);

        $expected = ['uno' => 'uno', 'dos' => 'dos', 'tres' => 'tres'];
        $this->assertInstanceOf(\Generator::class, $result);
        $this->assertEquals($expected, i\iterable_to_array($result, true));
    }

    public function testWithPreserveKeys()
    {
        $values = ['one' => 'uno', 'two' => 'dos', 'three' => 'tres'];
        $opts = [
            new FlagOption('foo'),
            $this->createMock(OptionInterface::class),
            new FlagOption('preserve_keys'),
        ];

        $step = new ItemAsKey();
        $result = $step($values, $opts);

        $this->assertEquals($values, $result);
    }
}
