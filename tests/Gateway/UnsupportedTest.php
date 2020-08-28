<?php

declare(strict_types=1);

namespace Jasny\Persist\Tests\Gateway;

use Jasny\Persist\Exception\UnsupportedFeatureException;
use Jasny\Persist\Gateway\Unsupported;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;

/**
 * @covers \Jasny\Persist\Gateway\Unsupported
 */
class UnsupportedTest extends TestCase
{
    protected Unsupported $gateway;

    public function setUp(): void
    {
        $this->gateway = new Unsupported();
    }

    public function testGetStorage()
    {
        $this->assertNull($this->gateway->getStorage());
    }

    public function testWithLogging()
    {
        /** @var LoggerInterface|MockObject $builder */
        $logger = $this->createMock(LoggerInterface::class);
        $ret = $this->gateway->withLogging($logger);

        $this->assertSame($this->gateway, $ret);
    }


    public function testFetch()
    {
        $this->expectException(UnsupportedFeatureException::class);

        $this->gateway->fetch();
    }

    public function testCount()
    {
        $this->expectException(UnsupportedFeatureException::class);

        $this->gateway->count();
    }

    public function testSave()
    {
        $this->expectException(UnsupportedFeatureException::class);

        $this->gateway->save([]);
    }

    public function testSaveAll()
    {
        $this->expectException(UnsupportedFeatureException::class);

        $this->gateway->saveAll([[], []]);
    }

    public function testUpdate()
    {
        $this->expectException(UnsupportedFeatureException::class);

        $this->gateway->update([], []);
    }

    public function testDelete()
    {
        $this->expectException(UnsupportedFeatureException::class);

        $this->gateway->delete([]);
    }
}
