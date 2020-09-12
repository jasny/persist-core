<?php

declare(strict_types=1);

namespace Persist\Tests\Query;

use Persist\Option\Functions as opt;
use Persist\Option\LookupOption;
use Persist\Option\OptionInterface;
use Persist\Query\NestLookup;
use PHPUnit\Framework\TestCase;

/**
 * @covers \Persist\Query\NestLookup
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
        $mockOpt1 = $this->createMock(OptionInterface::class);
        $mockOpt2 = $this->createMock(OptionInterface::class);

        $foo = opt\lookup('foo');
        $fooA = opt\lookup('foo_a')->for('foo');
        $fooB = opt\lookup('foo_b')->for('foo');
        $bar = opt\lookup('box')->as('bar');
        $barA = opt\lookup('bar_a')->for('bar')->as('a');
        $barA1 = opt\lookup('bar_a_1')->for('bar.a');

        $opts = [$mockOpt1, $foo, $fooA, $mockOpt2, $bar, $barA, $barA1, $fooB];

        $this->composer->prepare([], $opts);

        $this->assertCount(4, $opts);

        $this->assertSame($mockOpt1, $opts[0]);
        $this->assertSame($mockOpt2, $opts[2]);

        $this->assertInstanceOf(LookupOption::class, $opts[1]);
        $this->assertEquals('foo', $opts[1]->getRelated());
        $this->assertSame([$fooA, $fooB], $opts[1]->getOpts());
    }

    public function testNop()
    {
        $opts = [
            $this->createMock(OptionInterface::class),
            $this->createMock(OptionInterface::class),
        ];
        $expected = $opts;

        $this->composer->prepare([], $opts);

        $this->assertSame($expected, $opts);
    }
}
