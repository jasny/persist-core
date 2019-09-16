<?php

declare(strict_types=1);

namespace Jasny\DB\Tests\Write;

use Improved\IteratorPipeline\PipelineBuilder;
use Jasny\DB\Exception\UnsupportedFeatureException;
use Jasny\DB\QueryBuilder\QueryBuilderInterface;
use Jasny\DB\Write\NoWrite;
use PHPUnit\Framework\TestCase;

/**
 * @covers \Jasny\DB\Write\NoWrite
 */
class NoWriteTest extends TestCase
{
    public function testGetStorage()
    {
        $writer = new NoWrite();
        $this->assertNull($writer->getStorage());
    }


    public function testWithQueryBuilder()
    {
        $builder = $this->createMock(QueryBuilderInterface::class);

        $writer = new NoWrite();
        $ret = $writer->withQueryBuilder($builder);

        $this->assertSame($writer, $ret);
    }

    public function testWithSaveQueryBuilder()
    {
        $builder = $this->createMock(QueryBuilderInterface::class);

        $writer = new NoWrite();
        $ret = $writer->withSaveQueryBuilder($builder);

        $this->assertSame($writer, $ret);
    }

    public function testWithUpdateQueryBuilder()
    {
        $builder = $this->createMock(QueryBuilderInterface::class);

        $writer = new NoWrite();
        $ret = $writer->withUpdateQueryBuilder($builder);

        $this->assertSame($writer, $ret);
    }

    public function testWithResultBuilder()
    {
        $builder = $this->createMock(PipelineBuilder::class);

        $writer = new NoWrite();
        $ret = $writer->withResultBuilder($builder);

        $this->assertSame($writer, $ret);
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
