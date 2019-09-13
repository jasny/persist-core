<?php

declare(strict_types=1);

namespace Jasny\DB\Tests\Read;

use Improved\IteratorPipeline\PipelineBuilder;
use Jasny\DB\Exception\UnsupportedFeatureException;
use Jasny\DB\QueryBuilder\QueryBuilderInterface;
use Jasny\DB\Read\NoRead;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * @covers \Jasny\DB\Read\NoRead
 */
class NoReadTest extends TestCase
{
    public function testGetStorage()
    {
        $reader = new NoRead();
        $this->assertNull($reader->getStorage());
    }

    public function testWithQueryBuilder()
    {
        /** @var QueryBuilderInterface|MockObject $builder */
        $builder = $this->createMock(QueryBuilderInterface::class);

        $base = new NoRead();
        $ret = $base->withQueryBuilder($builder);

        $this->assertSame($base, $ret);
    }

    public function testWithResultBuilder()
    {
        $base = new NoRead();
        $ret = $base->withResultBuilder(new PipelineBuilder());

        $this->assertSame($base, $ret);
    }

    public function testFetch()
    {
        $this->expectException(UnsupportedFeatureException::class);

        $reader = new NoRead();
        $reader->fetch([], ['id' => 42]);
    }
    
    public function testCount()
    {
        $this->expectException(UnsupportedFeatureException::class);

        $reader = new NoRead();
        $reader->count([], ['id' => 42]);
    }
}
