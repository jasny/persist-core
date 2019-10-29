<?php

declare(strict_types=1);

namespace Jasny\DB\Tests;

use Jasny\DB\Exception\UnsupportedFeatureException;
use Jasny\DB\NoWrite;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;

/**
 * @covers \Jasny\DB\NoWrite
 */
class NoWriteTest extends TestCase
{
    protected NoWrite $writer;

    public function setUp(): void
    {
        $this->writer = new NoWrite();
    }

    public function testGetStorage()
    {
        $this->assertNull($this->writer->getStorage());
    }

    public function testWithLogging()
    {
        /** @var LoggerInterface|MockObject $builder */
        $logger = $this->createMock(LoggerInterface::class);
        $ret = $this->writer->withLogging($logger);

        $this->assertSame($this->writer, $ret);
    }


    public function testSave()
    {
        $this->expectException(UnsupportedFeatureException::class);

        $this->writer->save([]);
    }

    public function testSaveAll()
    {
        $this->expectException(UnsupportedFeatureException::class);

        $this->writer->saveAll([[], []]);
    }

    public function testUpdate()
    {
        $this->expectException(UnsupportedFeatureException::class);

        $this->writer->update([], []);
    }

    public function testDelete()
    {
        $this->expectException(UnsupportedFeatureException::class);

        $this->writer->delete([]);
    }
}
