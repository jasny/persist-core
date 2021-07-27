<?php

declare(strict_types=1);

namespace Jasny\Tests\Persist\Query;

use Jasny\Persist\Option\Functions as opt;
use Jasny\Persist\Option\LookupOption;
use Jasny\Persist\Option\OptionInterface;
use Jasny\Persist\Query\NestLookup;
use PHPUnit\Framework\TestCase;

/**
 * @covers \Jasny\Persist\Query\NestLookup
 */
class NestLookupTest extends TestCase
{
    protected NestLookup $composer;

    public function setUp(): void
    {
        $this->composer = new NestLookup();
    }

    public function test()
    {
        $acc = (object)[];

        $mockOpt1 = $this->createMock(OptionInterface::class);
        $mockOpt2 = $this->createMock(OptionInterface::class);

        $foo = opt\lookup('foo');
        $fooA = opt\lookup('foo_a')->for('foo');
        $fooB = opt\lookup('foo_b')->for('foo');
        $bar = opt\lookup('box')->as('bar');
        $barA = opt\lookup('bar_a')->for('bar')->as('a');
        $barA1 = opt\lookup('bar_a_1')->for('bar.a');

        $opts = [$mockOpt1, $foo, $fooA, $mockOpt2, $bar, $barA, $barA1, $fooB];

        $this->composer->compose($acc, [], $opts);

        $this->assertCount(4, $opts);

        $this->assertSame($mockOpt1, $opts[0]);
        $this->assertSame($mockOpt2, $opts[2]);

        $this->assertInstanceOf(LookupOption::class, $opts[1]);
        $this->assertEquals('foo', $opts[1]->getRelated());

        $this->assertEquals(
            [$fooA->for(null), $fooB->for(null)],
            $opts[1]->getOpts()
        );
    }

    public function testNop()
    {
        $acc = (object)[];

        $opts = [
            $this->createMock(OptionInterface::class),
            $this->createMock(OptionInterface::class),
        ];

        $expected = $opts;

        $this->composer->compose($acc, [], $opts);

        $this->assertSame($expected, $opts);
    }
}
