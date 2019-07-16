<?php

declare(strict_types=1);

namespace Jasny\DB\Tests\Write;

use Jasny\DB\Exception\UnsupportedFeatureException;
use Jasny\DB\QueryBuilder;
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
        $builder = $this->createMock(QueryBuilder::class);

        $base = new NoWrite();
        $ret = ([$base, $method])($builder);

        $this->assertSame($base, $ret);
    }

    public function testSave()
    {
        $this->expectException(UnsupportedFeatureException::class);

        $writer = new NoWrite();
        $writer->save([], [['id' => 42, 'foo' => 'bar']]);
    }

    public function testUpdate()
    {
        $this->expectException(UnsupportedFeatureException::class);

        $writer = new NoWrite();
        $writer->update([], ['id' => 42], []);
    }

    public function testDelete()
    {
        $this->expectException(UnsupportedFeatureException::class);

        $writer = new NoWrite();
        $writer->delete([], ['id' => 42]);
    }
}
