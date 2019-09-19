<?php

declare(strict_types=1);

namespace Jasny\DB\Tests\Result;

use Jasny\DB\FieldMap\FieldMapInterface;
use Jasny\DB\Result\Result;
use Jasny\DB\Result\ResultBuilder;
use PHPUnit\Framework\TestCase;

/**
 * @covers \Jasny\DB\Result\ResultBuilder
 */
class ResultBuilderTest extends TestCase
{
    public function testBasic()
    {
        $records = [
            ['name' => 'foo', 'number' => 9],
            ['name' => 'bar', 'number' => 42],
        ];

        $builder = new ResultBuilder();
        $result = $builder->with($records);

        $this->assertInstanceOf(Result::class, $result);
        $this->assertEquals($records, $result->toArray());
    }

    public function testWithFieldMap()
    {
        $records = [
            ['name' => 'foo', 'number' => 9],
            ['name' => 'bar', 'number' => 42],
        ];

        $expected = [
            ['NAME' => 'foo', 'nmbr' => 9],
            ['NAME' => 'bar', 'nmbr' => 42],
        ];

        $fieldMap = $this->createMock(FieldMapInterface::class);
        $fieldMap->expects($this->exactly(2))->method('__invoke')
            ->withConsecutive([$records[0]], [$records[1]])
            ->willReturnOnConsecutiveCalls(...$expected);

        $builder = new ResultBuilder($fieldMap);
        $result = $builder->with($records);

        $this->assertInstanceOf(Result::class, $result);
        $this->assertEquals($expected, $result->toArray());
    }

    public function testWithMeta()
    {
        $records = [];
        $meta = [
            'count' => 100,
            'filtered' => 0,
        ];

        $builder = new ResultBuilder();
        $result = $builder->with($records, $meta);

        $this->assertInstanceOf(Result::class, $result);
        $this->assertEquals($meta, $result->getMeta());
    }
}
