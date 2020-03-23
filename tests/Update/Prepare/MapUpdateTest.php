<?php

declare(strict_types=1);

namespace Jasny\DB\Tests\Update\Prepare;

use Jasny\DB\Map\NoMap;
use Jasny\DB\Update\Prepare\MapUpdate;
use Jasny\DB\Update\UpdateInstruction;
use Jasny\DB\Option\Functions as opts;
use Jasny\DB\Map\FieldMap;
use PHPUnit\Framework\TestCase;

/**
 * @covers \Jasny\DB\Update\Prepare\MapUpdate
 */
class MapUpdateTest extends TestCase
{
    protected const MAP = [
        'id' => '_id',
        'foo' => 'foos',
        'bar' => 'bor',
        'skippy' => false
    ];

    public function test()
    {
        $instructions = [
            new UpdateInstruction('set', ['id' => 42, 'bar' => 'hello']),
            new UpdateInstruction('inc', ['id' => 1, 'foo.bar.qux' => 9]),
            new UpdateInstruction('set', ['number' => 3]),
        ];

        $map = new FieldMap(self::MAP);
        $opts = [opts\setting('map', $map)];

        $applyTo = new MapUpdate();
        $mapped = $applyTo($instructions, $opts);

        $this->assertIsArray($mapped);
        $this->assertCount(3, $mapped);

        $this->assertEquals(new UpdateInstruction('set', ['_id' => 42, 'bor' => 'hello']), $mapped[0]);
        $this->assertEquals(new UpdateInstruction('inc', ['_id' => 1, 'foos.bar.qux' => 9]), $mapped[1]);
        $this->assertSame($instructions[2], $mapped[2]);
    }

    public function noMapProvider()
    {
        return [
            [[]],
            [[opts\setting('map', new NoMap())]],
        ];
    }

    /**
     * @dataProvider noMapProvider
     */
    public function testNoMap(array $opts)
    {
        $instructions = [
            new UpdateInstruction('set', ['id' => 42, 'bar' => 'hello']),
            new UpdateInstruction('inc', ['id' => 1, 'foo.bar.qux' => 9]),
            new UpdateInstruction('set', ['number' => 3]),
        ];

        $applyTo = new MapUpdate();
        $mapped = $applyTo($instructions, $opts);

        $this->assertSame($instructions, $mapped);
    }
}
