<?php

declare(strict_types=1);

namespace Jasny\DB\Tests\Map;

use Jasny\DB\Map\ChildMap;
use Jasny\DB\Map\ConfiguredMap;
use Jasny\DB\Map\DeepMap;
use Jasny\DB\Map\FlatMap;
use Jasny\DB\Map\MapInterface;
use Jasny\DB\Map\NestedMap;
use Jasny\DB\Map\NoMap;
use Jasny\PHPUnit\PrivateAccessTrait;
use PHPUnit\Framework\TestCase;

/**
 * @covers \Jasny\DB\Map\ConfiguredMap
 * @covers \Jasny\DB\Map\Traits\ProxyTrait
 */
class ConfiguredMapTest extends TestCase
{
    use PrivateAccessTrait;

    public function testWithNoMap()
    {
        $map = new ConfiguredMap([]);
        $this->assertInstanceOf(NoMap::class, $map->getInnerMap());
    }

    public function testWithFlatMap()
    {
        $map = new ConfiguredMap(['foo' => 'bar', 'one' => 'uno']);

        $this->assertInstanceOf(FlatMap::class, $map->getInnerMap());
        $this->assertEquals(new FlatMap(['foo' => 'bar', 'one' => 'uno']), $map->getInnerMap());
    }

    public function deepMapProvider()
    {
        return [
            'foo.id => bar' => [['foo.id' => 'bar']],
            'foo/id => bar' => [['foo/id' => 'bar']],
            'foo => bar.id' => [['foo' => 'bar.id']],
            'foo => bar/id' => [['foo' => 'bar/id']],
        ];
    }

    /**
     * @dataProvider deepMapProvider
     */
    public function testWithDeepMapForAppField(array $config)
    {
        $map = new ConfiguredMap($config);

        $this->assertInstanceOf(DeepMap::class, $map->getInnerMap());
        $this->assertEquals(new DeepMap($config), $map->getInnerMap());
    }


    public function testWithNestedMap()
    {
        $map = new ConfiguredMap(['abc[]' => ['a' => 'z', 'b' => 'y'], 'bar.qux' => ['one' => 'uno.id']]);

        /** @var NestedMap $inner */
        $inner = $map->getInnerMap();
        $this->assertInstanceOf(NestedMap::class, $inner);

        $nested = $inner->getInnerMaps();
        $this->assertCount(3, $nested);

        $this->assertArrayHasKey('', $nested);
        $this->assertInstanceOf(NoMap::class, $nested['']);

        $this->assertArrayHasKey('abc', $nested);
        $this->assertInstanceOf(ChildMap::class, $nested['abc']);
        $this->assertEquals(new FlatMap(['a' => 'z', 'b' => 'y']), $nested['abc']->getInnerMap());
        $this->assertTrue($nested['abc']->isForMany());

        $this->assertArrayHasKey('bar.qux', $nested);
        $this->assertInstanceOf(ChildMap::class, $nested['bar.qux']);
        $this->assertEquals(new DeepMap(['one' => 'uno.id']), $nested['bar.qux']->getInnerMap());
        $this->assertFalse($nested['bar.qux']->isForMany());
    }

    public function testWithNestedAndFlatMap()
    {
        $map = new ConfiguredMap(['one' => 'uno', 'abc[]' => ['a' => 'z', 'b' => 'y']]);

        /** @var NestedMap $inner */
        $inner = $map->getInnerMap();
        $this->assertInstanceOf(NestedMap::class, $inner);

        $nested = $inner->getInnerMaps();
        $this->assertCount(2, $nested);

        $this->assertArrayHasKey('', $nested);
        $this->assertEquals(new FlatMap(['one' => 'uno']), $nested['']);

        $this->assertArrayHasKey('abc', $nested);
        $this->assertInstanceOf(ChildMap::class, $nested['abc']);
        $this->assertEquals(new FlatMap(['a' => 'z', 'b' => 'y']), $nested['abc']->getInnerMap());
        $this->assertTrue($nested['abc']->isForMany());
    }

    /**
     * @dataProvider deepMapProvider
     */
    public function testWithNestedAndDeepMap(array $config)
    {
        $map = new ConfiguredMap($config + ['abc[]' => ['a' => 'z', 'b' => 'y']]);

        /** @var NestedMap $inner */
        $inner = $map->getInnerMap();
        $this->assertInstanceOf(NestedMap::class, $inner);

        $nested = $inner->getInnerMaps();
        $this->assertCount(2, $nested);

        $this->assertArrayHasKey('', $nested);
        $this->assertEquals(new DeepMap($config), $nested['']);

        $this->assertArrayHasKey('abc', $nested);
        $this->assertInstanceOf(ChildMap::class, $nested['abc']);
        $this->assertEquals(new FlatMap(['a' => 'z', 'b' => 'y']), $nested['abc']->getInnerMap());
        $this->assertTrue($nested['abc']->isForMany());
    }

    public function testToDB()
    {
        $map = new ConfiguredMap([]);

        $mock = $this->createMock(MapInterface::class);
        $this->setPrivateProperty($map, 'inner', $mock);

        $mock->expects($this->once())->method('toDB')->with('foo')->willReturn('bar');

        $this->assertEquals('bar', $map->toDB('foo'));
    }

    public function proxyMethodProvider()
    {
        return [
            'forFilter' => ['forFilter'],
            'forUpdate' => ['forUpdate'],
            'forResult' => ['forResult'],
            'forItems' => ['forItems'],
        ];
    }

    /**
     * @dataProvider proxyMethodProvider
     */
    public function testProxyMethods(string $method)
    {
        $map = new ConfiguredMap([]);

        $mock = $this->createMock(MapInterface::class);
        $this->setPrivateProperty($map, 'inner', $mock);

        $fn = fn() => null;
        $mock->expects($this->once())->method($method)->willReturn($fn);

        $this->assertSame($fn, $map->{$method}());
    }
}
