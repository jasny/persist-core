<?php

declare(strict_types=1);

namespace Jasny\DB\Tests\Map;

use Jasny\DB\Filter\FilterItem;
use Jasny\DB\Map\NoMap;
use Jasny\DB\Update\UpdateInstruction;
use PHPUnit\Framework\TestCase;

/**
 * @covers \Jasny\DB\Map\NoMap
 */
class NoMapTest extends TestCase
{
    protected NoMap $map;

    public function setUp(): void
    {
        $this->map = new NoMap();
    }

    public function testWithOpts()
    {
        $this->assertSame($this->map, $this->map->withOpts([]));
    }

    public function testApplyToField()
    {
        $this->assertNull($this->map->applyToField('foo'));
    }

    public function testApply()
    {
        $array = [new FilterItem('foo', '', 1)];
        $this->assertSame($array, $this->map->apply($array));
    }

    public function testApplyInverse()
    {
        $array = [new UpdateInstruction('set', ['foo' => 1])];
        $this->assertEquals($array, $this->map->applyInverse($array));
    }
}
