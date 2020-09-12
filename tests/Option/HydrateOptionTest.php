<?php

declare(strict_types=1);

namespace Persist\Tests\Option;

use Persist\Option\Functions as opt;
use Persist\Option\HydrateOption;
use PHPUnit\Framework\TestCase;

/**
 * @covers \Persist\Option\HydrateOption
 */
class HydrateOptionTest extends TestCase
{
    /**
     * @covers \Persist\Option\Functions\hydrate
     */
    public function testHydrate()
    {
        $this->assertEquals(new HydrateOption('foo'), opt\hydrate('foo'));
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
        $this->assertEquals($field, opt\hydrate($field)->getField());
        $this->assertEquals(substr($field, 0, 3), opt\hydrate($field)->getName());
    }

    public function testHydrateAs()
    {
        $opt = opt\hydrate('foo_number')->as('foo');

        $this->assertEquals('foo_number', $opt->getField());
        $this->assertEquals('foo', $opt->getName());
    }
}
