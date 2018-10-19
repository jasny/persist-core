<?php

namespace Jasny\DB\Tests\Update;

use Jasny\DB\Update\UpdateOperation;
use Jasny\DB\Update\UpdateParser;
use PHPUnit\Framework\TestCase;

/**
 * @covers \Jasny\DB\Update\UpdateParser
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
            $this->createUpdateOperationMock(['getOperator' => 'set', 'getField' => 'foo', 'getValue' => 42]),
            $this->createUpdateOperationMock(['getOperator' => 'add', 'getField' => 'bar', 'getValue' => 99]),
            $this->createUpdateOperationMock(['getOperator' => 'set', 'getField' => ['color' => 'blue', 'age' => 21]])
        ];

        $parse = new UpdateParser();

        $iterator = $parse($operations);

        $expected = [
            [['field' => 'foo', 'operator' => 'set'], 42],
            [['field' => 'bar', 'operator' => 'add'], 99],
            [['field' => 'color', 'operator' => 'set'], 'blue'],
            [['field' => 'age', 'operator' => 'set'], 21]
        ];
        $i = 0;

        foreach ($iterator as $key => $value) {
            $this->assertEquals($expected[$i][0], $key);
            $this->assertEquals($expected[$i][1], $value);
            $i++;
        }
    }
}
