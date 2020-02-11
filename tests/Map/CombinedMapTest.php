<?php

declare(strict_types=1);

namespace Jasny\DB\Tests\Map;

use Improved as i;
use Jasny\DB\Filter\FilterItem;
use Jasny\DB\Map\CombinedMap;
use Jasny\DB\Map\MapInterface;
use Jasny\DB\Update\UpdateInstruction;
use Jasny\PHPUnit\CallbackMockTrait;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * @covers \Jasny\DB\Map\CombinedMap
 * @covers \Jasny\DB\Map\Traits\CombineTrait
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


    public function testToDB()
    {
        $this->inner[0]->expects($this->once())->method('toDB')->with('a')->willReturn('b');
        $this->inner[1]->expects($this->once())->method('toDB')->with('b')->willReturn('c');
        $this->inner[2]->expects($this->once())->method('toDB')->with('c')->willReturn('d');

        $this->assertEquals('d', $this->map->toDB('a'));
    }

    public function testToDBWithIgnoredField()
    {
        $this->inner[0]->expects($this->once())->method('toDB')->with('a')->willReturn('b');
        $this->inner[1]->expects($this->once())->method('toDB')->with('b')->willReturn(false);
        $this->inner[2]->expects($this->never())->method('toDB');

        $this->assertFalse($this->map->toDB('a'));
    }

    public function testForFilter()
    {
        $filterItems = [
            [new FilterItem('a', '', 0)],
            [new FilterItem('b', '', 0)],
            [new FilterItem('c', '', 0)],
            [new FilterItem('d', '', 0)],
        ];

        foreach ($this->inner as $i => $inner) {
            $callback = $this->createCallbackMock($this->once(), [$filterItems[$i]], $filterItems[$i + 1]);
            $inner->expects($this->once())->method('forFilter')->willReturn($callback);
        }

        $applyTo = $this->map->forFilter();
        $result = $applyTo($filterItems[0]);

        $this->assertSame($filterItems[3], $result);
    }

    public function testForUpdate()
    {
        $instructions = [
            [new UpdateInstruction('set', ['a' => 1])],
            [new UpdateInstruction('set', ['b' => 1])],
            [new UpdateInstruction('set', ['c' => 1])],
            [new UpdateInstruction('set', ['d' => 1])],
        ];

        foreach ($this->inner as $i => $inner) {
            $callback = $this->createCallbackMock($this->once(), [$instructions[$i]], $instructions[$i + 1]);
            $inner->expects($this->once())->method('forUpdate')->willReturn($callback);
        }

        $applyTo = $this->map->forUpdate();
        $result = $applyTo($instructions[0]);

        $this->assertSame($instructions[3], $result);
    }

    public function testForResult()
    {
        $items = [
            [(object)['a' => 1]],
            [(object)['b' => 1]],
            [(object)['c' => 1]],
            [(object)['d' => 1]],
        ];

        foreach ($this->inner as $i => $inner) {
            $match = $this->callback(fn($a) => is_iterable($a) && i\iterable_to_array($a, true) === $items[$i + 1]);
            $callback = $this->createCallbackMock($this->once(), [$match], $items[$i]);
            $inner->expects($this->once())->method('forResult')->willReturn($callback);
        }

        $applyTo = $this->map->forResult();
        $result = $applyTo($items[3]);

        $this->assertSame($items[0], $result);
    }

    public function testForItems()
    {
        $items = [
            [(object)['a' => 1]],
            [(object)['b' => 1]],
            [(object)['c' => 1]],
            [(object)['d' => 1]],
        ];

        foreach ($this->inner as $i => $inner) {
            $match = $this->callback(fn($a) => is_iterable($a) && i\iterable_to_array($a, true) === $items[$i]);
            $callback = $this->createCallbackMock($this->once(), [$match], $items[$i + 1]);
            $inner->expects($this->once())->method('forItems')->willReturn($callback);
        }

        $applyTo = $this->map->forItems();
        $result = $applyTo($items[0]);

        $this->assertSame($items[3], $result);
    }
}
