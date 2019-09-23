<?php

declare(strict_types=1);

namespace Jasny\DB\Tests\QueryBuilder\Prepare;

use Jasny\DB\QueryBuilder\Prepare\UpdateParser;
use Jasny\DB\Update\UpdateOperation;
use PHPUnit\Framework\TestCase;

/**
 * @covers \Jasny\DB\QueryBuilder\Prepare\UpdateParser
 */
class UpdateParserTest extends TestCase
{
    protected function createUpdateOperationMock(array $config)
    {
        return $this->createConfiguredMock(UpdateOperation::class, $config);
    }

    public function test()
    {
        $operations = [
            $this->createUpdateOperationMock(['getOperator' => 'set', 'getPairs' => ['foo' => 42]]),
            $this->createUpdateOperationMock(['getOperator' => 'add', 'getPairs' => ['bar' => 99]]),
            $this->createUpdateOperationMock(['getOperator' => 'set', 'getPairs' => ['color' => 'blue', 'age' => 21]])
        ];

        $parse = new UpdateParser();

        $iterator = $parse($operations, []);

        $expected = [
            [['field' => 'foo', 'operator' => 'set'], 42],
            [['field' => 'bar', 'operator' => 'add'], 99],
            [['field' => 'color', 'operator' => 'set'], 'blue'],
            [['field' => 'age', 'operator' => 'set'], 21]
        ];

        $index = 0;

        foreach ($iterator as $key => $value) {
            $this->assertEquals($expected[$index][0], $key);
            $this->assertEquals($expected[$index][1], $value);

            $index++;
        }
    }
}
