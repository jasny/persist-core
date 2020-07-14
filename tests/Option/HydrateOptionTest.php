<?php

declare(strict_types=1);

namespace Jasny\DB\Tests\Option;

use Jasny\DB\Option\Functions as opts;
use Jasny\DB\Option\HydrateOption;
use PHPUnit\Framework\TestCase;

/**
 * @covers \Jasny\DB\Option\HydrateOption
 */
class HydrateOptionTest extends TestCase
{
    /**
     * @covers \Jasny\DB\Option\Functions\hydrate
     */
    public function testHydrate()
    {
        $this->assertEquals(new HydrateOption('foo'), opts\hydrate('foo'));
    }

    public function fieldProvider()
    {
        return [
            'foo' => ['foo'],
            'foo_id' => ['foo_id'],
            'FooId' => ['FooId'],
            'FooID' => ['FooID'],
        ];
    }

    /**
     * @dataProvider fieldProvider
     */
    public function testConstruct(string $field)
    {
        $this->assertEquals($field, opts\hydrate($field)->getField());
        $this->assertEquals(substr($field, 0, 3), opts\hydrate($field)->getName());
    }

    public function testHydrateAs()
    {
        $opt = opts\hydrate('foo_number')->as('foo');

        $this->assertEquals('foo_number', $opt->getField());
        $this->assertEquals('foo', $opt->getName());
    }
}
