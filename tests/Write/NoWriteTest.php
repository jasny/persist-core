<?php

namespace Jasny\DB\Tests\Write;

use Improved as i;
use Jasny\DB\QueryBuilder\QueryBuilderInterface;
use Jasny\DB\Update\UpdateOperation;
use Jasny\DB\Write\NoWrite;
use PHPUnit\Framework\TestCase;

/**
 * @covers \Jasny\DB\Write\NoWrite
 */
class NoWriteTest extends TestCase
{
    public function queryBuilderMethodProvider()
    {
        return [
            ['withQueryBuilder'],
            ['withSaveQueryBuilder'],
            ['withUpdateQueryBuilder']
        ];
    }

    /**
     * @dataProvider queryBuilderMethodProvider
     */
    public function testWithQueryBuilder($method)
    {
        $builder = $this->createMock(QueryBuilderInterface::class);

        $base = new NoWrite();
        $ret = i\function_call([$base, $method], $builder);

        $this->assertSame($base, $ret);
    }

    /**
     * @expectedException \Jasny\DB\Exception\UnsupportedFeatureException
     */
    public function testSave()
    {
        $writer = new NoWrite();

        $writer->save([], [['id' => 42, 'foo' => 'bar']]);
    }

    /**
     * @expectedException \Jasny\DB\Exception\UnsupportedFeatureException
     */
    public function testUpdate()
    {
        $writer = new NoWrite();

        $writer->update([], ['id' => 42], []);
    }

    /**
     * @expectedException \Jasny\DB\Exception\UnsupportedFeatureException
     */
    public function testDelete()
    {
        $writer = new NoWrite();

        $writer->delete([], ['id' => 42]);
    }
}
