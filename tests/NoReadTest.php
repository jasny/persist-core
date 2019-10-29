<?php

declare(strict_types=1);

namespace Jasny\DB\Tests;

use Jasny\DB\Exception\UnsupportedFeatureException;
use Jasny\DB\NoRead;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;

/**
 * @covers \Jasny\DB\NoRead
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
