<?php

declare(strict_types=1);

namespace Jasny\Persist\Tests\Gateway;

use Jasny\Persist\Option\OptionInterface;
use Jasny\Persist\Result\Result;
use Jasny\Persist\Update\UpdateInstruction;
use Jasny\Persist\Gateway\MultiWrite;
use Jasny\Persist\Gateway\GatewayInterface;
use Jasny\PHPUnit\SafeMocksTrait;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;

/**
 * @covers \Jasny\Persist\Gateway\MultiWrite
 */
class MultiWriteTest extends TestCase
{
    use SafeMocksTrait;

    protected MultiWrite $gateway;

    /** @var OptionInterface[] */
    protected array $opts;

    /** @var GatewayInterface[]|MockObject[] */
    protected array $inner;

    public function setUp(): void
    {
        $this->opts = [
            $this->createStub(OptionInterface::class),
            $this->createStub(OptionInterface::class),
            $this->createStub(OptionInterface::class),
        ];

        $this->inner = [
            $this->createMock(GatewayInterface::class),
            $this->createMock(GatewayInterface::class),
            $this->createMock(GatewayInterface::class),
        ];

        $this->gateway = new MultiWrite(...$this->inner);
    }

    public function testGetInner()
    {
        $this->assertSame($this->inner, $this->gateway->getInner());
    }

    public function testGetStorage()
    {
        $storage = new \stdClass();

        $this->inner[0]->expects($this->once())->method('getStorage')->willReturn($storage);
        $this->inner[1]->expects($this->never())->method('getStorage');
        $this->inner[2]->expects($this->never())->method('getStorage');

        $this->assertSame($storage, $this->gateway->getStorage());
    }

    public function testWithLogging()
    {
        $innerWithLogging = [
            $this->createMock(GatewayInterface::class),
            $this->createMock(GatewayInterface::class),
            $this->createMock(GatewayInterface::class),
        ];

        $logger = $this->createMock(LoggerInterface::class);

        foreach ($this->inner as $index => $writer) {
            $writer->expects($this->once())->method('withLogging')
                ->with($this->identicalTo($logger))
                ->willReturn($innerWithLogging[$index]);
        }

        $gatewayWithLogging = $this->gateway->withLogging($logger);

        $this->assertInstanceOf(MultiWrite::class, $gatewayWithLogging);
        $this->assertSame($innerWithLogging, $gatewayWithLogging->getInner());

        $this->assertNotSame($this->gateway, $gatewayWithLogging);
        $this->assertSame($this->inner, $this->gateway->getInner()); // Test immutability
    }

    public function testWithLoggingIdempotent()
    {
        $logger = $this->createMock(LoggerInterface::class);

        foreach ($this->inner as $index => $writer) {
            $writer->expects($this->once())->method('withLogging')
                ->with($this->identicalTo($logger))
                ->willReturnSelf();
        }

        $gatewayWithLogging = $this->gateway->withLogging($logger);

        $this->assertSame($this->gateway, $gatewayWithLogging);
    }


    public function testFetch()
    {
        $result = $this->createMock(Result::class);

        $this->inner[0]->expects($this->once())->method('fetch')
            ->with(['foo' => 1], ...$this->opts)
            ->willReturn($result);
        $this->inner[1]->expects($this->never())->method('fetch');
        $this->inner[2]->expects($this->never())->method('fetch');

        $fetched = $this->gateway->fetch(['foo' => 1], ...$this->opts);

        $this->assertSame($result, $fetched);
    }

    public function testCount()
    {
        $this->inner[0]->expects($this->once())->method('count')
            ->with(['foo' => 1], ...$this->opts)
            ->willReturn(10);
        $this->inner[1]->expects($this->never())->method('count');
        $this->inner[2]->expects($this->never())->method('count');

        $count = $this->gateway->count(['foo' => 1], ...$this->opts);

        $this->assertEquals(10, $count);
    }


    public function testSave()
    {
        $itemConsecutive = [
            new \stdClass(),
            new \stdClass(),
            new \stdClass(),
        ];

        $results = [
            $this->createMock(Result::class),
            $this->createMock(Result::class),
            $this->createMock(Result::class),
        ];
        $results[0]->expects($this->once())->method('first')->willReturn($itemConsecutive[1]);
        $results[1]->expects($this->once())->method('first')->willReturn($itemConsecutive[2]);
        $results[2]->expects($this->never())->method('first');

        foreach ($this->inner as $index => $writer) {
            $writer->expects($this->once())->method('save')
                ->with(
                    $this->identicalTo($itemConsecutive[$index]),
                    $this->identicalTo($this->opts[0]),
                    $this->identicalTo($this->opts[1]),
                    $this->identicalTo($this->opts[2]),
                )
                ->willReturn($results[$index]);
        }

        $result = $this->gateway->save($itemConsecutive[0], ...$this->opts);

        $this->assertSame($results[2], $result);
    }

    public function testSaveAll()
    {
        $items = [new \stdClass(), new \stdClass()];

        $results = [
            $this->createMock(Result::class),
            $this->createMock(Result::class),
            $this->createMock(Result::class),
        ];

        foreach ($this->inner as $index => $writer) {
            $writer->expects($this->once())->method('saveAll')
                ->with(
                    $index === 0
                        ? $this->callback(fn($result) => $result instanceof Result && $result->toArray() === $items)
                        : $this->identicalTo($results[$index - 1]),
                    $this->identicalTo($this->opts[0]),
                    $this->identicalTo($this->opts[1]),
                    $this->identicalTo($this->opts[2]),
                )
                ->willReturn($results[$index]);
        }

        $result = $this->gateway->saveAll($items, ...$this->opts);

        $this->assertSame($results[2], $result);
    }

    public function testUpdate()
    {
        $filter = [
            'foo' => 'bar',
            'color' => 'red',
        ];
        $instruction = $this->createMock(UpdateInstruction::class);

        $results = [
            $this->createMock(Result::class),
            $this->createMock(Result::class),
            $this->createMock(Result::class),
        ];

        foreach ($this->inner as $index => $writer) {
            $writer->expects($this->once())->method('update')
                ->with(
                    $filter,
                    $this->identicalTo($instruction),
                    $this->identicalTo($this->opts[0]),
                    $this->identicalTo($this->opts[1]),
                    $this->identicalTo($this->opts[2]),
                )
                ->willReturn($results[$index]);
        }

        $result = $this->gateway->update($filter, $instruction, ...$this->opts);

        $this->assertSame($results[0], $result);
    }

    public function testDelete()
    {
        $filter = [
            'foo' => 'bar',
            'color' => 'red',
        ];

        $results = [
            $this->createMock(Result::class),
            $this->createMock(Result::class),
            $this->createMock(Result::class),
        ];

        foreach ($this->inner as $index => $writer) {
            $writer->expects($this->once())->method('delete')
                ->with(
                    $filter,
                    $this->identicalTo($this->opts[0]),
                    $this->identicalTo($this->opts[1]),
                    $this->identicalTo($this->opts[2]),
                )
                ->willReturn($results[$index]);
        }

        $result = $this->gateway->delete($filter, ...$this->opts);

        $this->assertSame($results[0], $result);
    }
}
