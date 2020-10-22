<?php

declare(strict_types=1);

namespace Jasny\Tests\Persist\Map;

use Jasny\Persist\Map\CombinedMap;
use Jasny\Persist\Map\MapInterface;
use Jasny\PHPUnit\CallbackMockTrait;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * @covers \Jasny\Persist\Map\CombinedMap
 * @covers \Jasny\Persist\Map\Traits\CombineTrait
 */
class CombinedMapTest extends TestCase
{
    use CallbackMockTrait;

    /** @var MockObject[]|MapInterface[] */
    protected array $inner = [];

    protected CombinedMap $map;

    public function setUp(): void
    {
        for ($i = 0; $i < 3; $i++) {
            $this->inner[] = $this->createMock(MapInterface::class);
        }

        $this->map = new CombinedMap(...$this->inner);
    }

    public function testWithOpts()
    {
        $this->assertSame($this->map, $this->map->withOpts([]));
    }

    public function testGetInner()
    {
        $this->assertSame($this->inner, $this->map->getInner());
    }

    public function testApplyToField()
    {
        $this->inner[0]->expects($this->once())->method('applyToField')->with('a')->willReturn('b');
        $this->inner[1]->expects($this->once())->method('applyToField')->with('b')->willReturn('c');
        $this->inner[2]->expects($this->once())->method('applyToField')->with('c')->willReturn('d');

        $this->assertEquals('d', $this->map->applyToField('a'));
    }

    public function testApplyToFieldWithIgnoredField()
    {
        $this->inner[0]->expects($this->once())->method('applyToField')->with('a')->willReturn('b');
        $this->inner[1]->expects($this->once())->method('applyToField')->with('b')->willReturn(false);
        $this->inner[2]->expects($this->never())->method('applyToField');

        $this->assertFalse($this->map->applyToField('a'));
    }

    public function testApply()
    {
        $items = [
            (object)['a' => 1],
            (object)['b' => 1],
            (object)['c' => 1],
            (object)['d' => 1],
        ];

        foreach ($this->inner as $i => $inner) {
            $inner->expects($this->once())->method('apply')
                ->with($this->identicalTo($items[$i]))
                ->willReturn($items[$i + 1]);
        }

        $mapped = $this->map->apply($items[0]);

        $this->assertSame($items[3], $mapped);
    }

    public function testApplyInverse()
    {
        $items = [
            (object)['a' => 1],
            (object)['b' => 1],
            (object)['c' => 1],
            (object)['d' => 1],
        ];

        foreach ($this->inner as $i => $inner) {
            $inner->expects($this->once())->method('applyInverse')
                ->with($this->identicalTo($items[$i + 1]))
                ->willReturn($items[$i]);
        }

        $mapped = $this->map->applyInverse($items[3]);

        $this->assertSame($items[0], $mapped);
    }
}
