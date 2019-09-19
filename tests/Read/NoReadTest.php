<?php

declare(strict_types=1);

namespace Jasny\DB\Tests\Read;

use Jasny\DB\Exception\UnsupportedFeatureException;
use Jasny\DB\QueryBuilder\QueryBuilderInterface;
use Jasny\DB\Read\NoRead;
use Jasny\DB\Result\ResultBuilder;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;

/**
 * @covers \Jasny\DB\Read\NoRead
 */
class NoReadTest extends TestCase
{
    protected NoRead $reader;

    public function setUp(): void
    {
        $this->reader = new NoRead();
    }

    
    public function testGetStorage()
    {
        $this->assertNull($this->reader->getStorage());
    }

    public function testWithLogging()
    {
        /** @var LoggerInterface|MockObject $builder */
        $logger = $this->createMock(LoggerInterface::class);
        $ret = $this->reader->withLogging($logger);

        $this->assertSame($this->reader, $ret);
    }

    public function testWithResultBuilder()
    {
        /** @var ResultBuilder|MockObject $builder */
        $builder = $this->createMock(ResultBuilder::class);
        $ret = $this->reader->withResultBuilder($builder);

        $this->assertSame($this->reader, $ret);
    }

    public function testWithQueryBuilder()
    {
        /** @var QueryBuilderInterface|MockObject $builder */
        $builder = $this->createMock(QueryBuilderInterface::class);
        $ret = $this->reader->withQueryBuilder($builder);

        $this->assertSame($this->reader, $ret);
    }

    public function testFetch()
    {
        $this->expectException(UnsupportedFeatureException::class);

        $this->reader->fetch([], ['id' => 42]);
    }
    
    public function testCount()
    {
        $this->expectException(UnsupportedFeatureException::class);

        $this->reader->count([], ['id' => 42]);
    }
}
