<?php

declare(strict_types=1);

namespace Persist\Tests\Result;

use Improved\IteratorPipeline\Pipeline;
use Persist\Result\Result;
use PHPUnit\Framework\TestCase;

/**
 * @covers \Persist\Result\Result
 */
class ResultTest extends TestCase
{
    public function testPipeline()
    {
        $result = new Result([]);
        $this->assertInstanceOf(Pipeline::class, $result);
    }

    public function testGetMeta()
    {
        $result = new Result([], ['total' => 42]);

        $this->assertEquals(['total' => 42], $result->getMeta());
    }

    public function testGetMetaNotSet()
    {
        $result = new Result([]);

        $this->assertEquals([], $result->getMeta());
    }

    public function testGetMetaUsingKey()
    {
        $result = new Result([], ['total' => 42]);

        $this->assertEquals(42, $result->getMeta('total'));
        $this->assertNull($result->getMeta('other'));
    }


    public function applyCastProvider()
    {
        $asArray = fn($item) => $item;
        $asObject = fn($item) => (object)$item;

        return [
            //'array / array'   => [$asArray, $asArray],
            //'array / object'  => [$asArray, $asObject],
            'object / array'  => [$asObject, $asArray],
            'object / object' => [$asObject, $asObject],
        ];
    }

    /**
     * @dataProvider applyCastProvider
     */
    public function testApplyTo(callable $castRecords, callable $castItems)
    {
        $records = [
            ['id' => 1, 'time' => new \DateTime('2020-01-01T00:00:01+00:00')],
            ['id' => 2, 'time' => new \DateTime('2020-01-01T00:00:02+00:00')],
            ['id' => 3, 'time' => new \DateTime('2020-01-01T00:00:03+00:00')],
        ];

        $items = [
            ['id' => null, 'name' => 'one'],
            ['id' => null, 'name' => 'two'],
            ['id' => null, 'name' => 'three'],
        ];

        $expected = [
            ['id' => 1, 'name' => 'one', 'time' => new \DateTime('2020-01-01T00:00:01+00:00')],
            ['id' => 2, 'name' => 'two', 'time' => new \DateTime('2020-01-01T00:00:02+00:00')],
            ['id' => 3, 'name' => 'three', 'time' => new \DateTime('2020-01-01T00:00:03+00:00')],
        ];

        $result = new Result(array_map($castRecords, $records));
        $result->applyTo(array_map($castItems, $items));

        $this->assertEquals(array_map($castItems, $expected), $result->toArray());
    }

    public function testApplyToUnserialize()
    {
        $record = ['id' => 1, 'time' => new \DateTime('2020-01-01T00:00:01+00:00')];

        $item = new class() {
            public $data;

            public function __unserialize(array $data): void
            {
                $this->data = $data;
            }
        };

        $result = new Result([$record]);
        $result->applyTo([$item]);

        $this->assertSame([$item], $result->toArray());
        $this->assertSame($record, $item->data);
    }
}
