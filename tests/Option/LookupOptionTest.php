<?php

declare(strict_types=1);

namespace Jasny\DB\Tests\Option;

use Jasny\DB\Option\Functions as opt;
use Jasny\DB\Option\LookupOption;
use PHPUnit\Framework\TestCase;

/**
 * @covers \Jasny\DB\Option\LookupOption
 */
class LookupOptionTest extends TestCase
{
    /**
     * @covers \Jasny\DB\Option\Functions\lookup
     */
    public function testLookup()
    {
        $this->assertEquals(new LookupOption('foo'), opt\lookup('foo'));
    }

    public function collectionProvider()
    {
        return [
            'foo' => ['foo', 'foo'],
            'foo:default' => ['foo:default', 'foo_default'],
        ];
    }

    /**
     * @dataProvider collectionProvider
     */
    public function testConstruct(string $collection, string $name)
    {
        $this->assertEquals($collection, opt\lookup($collection)->getRelated());
        $this->assertEquals($name, opt\lookup($collection)->getName());
    }

    public function testLookupAs()
    {
        $opt = opt\lookup('foo')->as('foos');

        $this->assertEquals('foo', $opt->getRelated());
        $this->assertEquals('foos', $opt->getName());
    }

    public function testHaving()
    {
        $opt = opt\lookup('foo')->having(['abc' => 10]);

        $this->assertEquals(['abc' => 10], $opt->getFilter());
    }
}
