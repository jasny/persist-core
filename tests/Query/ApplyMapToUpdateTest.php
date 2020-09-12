<?php

declare(strict_types=1);

namespace Persist\Tests\Query;

use Improved as i;
use Persist\Map\NoMap;
use Persist\Query\ApplyMapToUpdate;
use Persist\Update\UpdateInstruction;
use Persist\Option\Functions as opt;
use Persist\Map\FieldMap;
use PHPUnit\Framework\TestCase;

/**
 * @covers \Persist\Query\ApplyMapToUpdate
 */
class ApplyMapToUpdateTest extends TestCase
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
        $opts = [opt\setting('map', $map)];

        $applyMap = new ApplyMapToUpdate();
        $iterator = $applyMap->prepare($instructions, $opts);
        $mapped = i\iterable_to_array($iterator);

        $this->assertCount(3, $mapped);

        $this->assertEquals(new UpdateInstruction('set', ['_id' => 42, 'bor' => 'hello']), $mapped[0]);
        $this->assertEquals(new UpdateInstruction('inc', ['_id' => 1, 'foos.bar.qux' => 9]), $mapped[1]);
        $this->assertSame($instructions[2], $mapped[2]);
    }

    public function noMapProvider()
    {
        return [
            [[]],
            [[opt\setting('map', new NoMap())]],
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

        $applyMap = new ApplyMapToUpdate();
        $mapped = $applyMap->prepare($instructions, $opts);

        $this->assertSame($instructions, $mapped);
    }
}
