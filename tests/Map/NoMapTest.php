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

    public function testToDB()
    {
        $this->assertEquals('foo', $this->map->toDB('foo'));
    }

    public function testForFilter()
    {
        $applyTo = $this->map->forFilter();

        $array = [new FilterItem('foo', '', 1)];
        $this->assertSame($array, $applyTo($array));
    }

    public function testForUpdate()
    {
        $applyTo = $this->map->forUpdate();

        $array = [new UpdateInstruction('set', ['foo' => 1])];
        $this->assertEquals($array, $applyTo($array));
    }

    public function testForResult()
    {
        $applyTo = $this->map->forResult();

        $array = [(object)['foo' => 1]];
        $this->assertEquals($array, $applyTo($array));
    }

    public function testForItems()
    {
        $applyTo = $this->map->forItems();

        $array = [(object)['foo' => 1]];
        $this->assertEquals($array, $applyTo($array));
    }
}
