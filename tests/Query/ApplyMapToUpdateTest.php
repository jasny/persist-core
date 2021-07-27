<?php

declare(strict_types=1);

namespace Jasny\Tests\Persist\Query;

use Improved as i;
use Jasny\Persist\Map\NoMap;
use Jasny\Persist\Query\ApplyMapToUpdate;
use Jasny\Persist\Update\UpdateInstruction;
use Jasny\Persist\Option\Functions as opt;
use Jasny\Persist\Map\FieldMap;
use PHPUnit\Framework\TestCase;

/**
 * @covers \Jasny\Persist\Query\ApplyMapToUpdate
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
        $acc = (object)[];
        $instructions = [
            new UpdateInstruction('set', ['id' => 42, 'bar' => 'hello']),
            new UpdateInstruction('inc', ['id' => 1, 'foo.bar.qux' => 9]),
            new UpdateInstruction('set', ['number' => 3]),
        ];

        $map = new FieldMap(self::MAP);
        $opts = [opt\setting('map', $map)];

        $applyMap = new ApplyMapToUpdate();
        $iterator = $applyMap->compose($acc, $instructions, $opts);
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
        $acc = (object)[];
        $instructions = [
            new UpdateInstruction('set', ['id' => 42, 'bar' => 'hello']),
            new UpdateInstruction('inc', ['id' => 1, 'foo.bar.qux' => 9]),
            new UpdateInstruction('set', ['number' => 3]),
        ];

        $applyMap = new ApplyMapToUpdate();
        $mapped = $applyMap->compose($acc, $instructions, $opts);

        $this->assertSame($instructions, $mapped);
    }
}
