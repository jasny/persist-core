<?php

declare(strict_types=1);

namespace Jasny\DB\Tests\Option;

use Jasny\DB\Option\Functions as opts;
use Jasny\DB\Option\LookupOption;
use PHPUnit\Framework\TestCase;

/**
 * @covers \Jasny\DB\Option\LookupOption
 */
class LookupOptionTest extends TestCase
{
    public function testWithoutRelated()
    {
        $option = new LookupOption('foo');

        $this->assertEquals('foo', $option->getField());
        $this->assertNull($option->getRelatedCollection());
        $this->assertNull($option->getMatch());
    }

    public function testWithRelated()
    {
        $option = new LookupOption('foo', 'foos', ['id' => 'foo_id']);

        $this->assertEquals('foo', $option->getField());
        $this->assertEquals('foos', $option->getRelatedCollection());
        $this->assertEquals(['id' => 'foo_id'], $option->getMatch());
    }

    /**
     * @covers \Jasny\DB\Option\Functions\hydrate
     */
    public function testHydrate()
    {
        $this->assertEquals(new LookupOption('foo'), opts\hydrate('foo'));
    }

    /**
     * @covers \Jasny\DB\Option\Functions\lookup
     */
    public function testLookup()
    {
        $this->assertEquals(new LookupOption('foo', 'foo'), opts\lookup('foo'));
        $this->assertEquals(
            new LookupOption('foo', 'foo', ['id' => 'foo_id']),
            opts\lookup('foo', ['id' => 'foo_id'])
        );
    }

    public function testLookupAs()
    {
        $this->assertEquals(new LookupOption('foo', 'foos'), opts\lookup('foos')->as('foo'));
    }

    public function testHydrateAs()
    {
        $this->expectException(\LogicException::class);
        $this->expectExceptionMessage("Unable to change field name when expanding specific field");

        opts\hydrate('foo')->as('bar');
    }
}
