<?php

declare(strict_types=1);

namespace Jasny\DB\Tests\Writer;

use Jasny\DB\Option\Functions as opts;
use Jasny\DB\Option\OptionInterface;
use Jasny\DB\Result\Result;
use Jasny\DB\Update\UpdateInstruction;
use Jasny\DB\Writer\MultiWrite;
use Jasny\DB\Writer\WriteInterface;
use Jasny\PHPUnit\SafeMocksTrait;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;

/**
 * @covers \Jasny\DB\Writer\MultiWrite
 */
class MultiWriteTest extends TestCase
{
    use SafeMocksTrait;

    protected MultiWrite $multiWrite;

    /** @var WriteInterface[]|MockObject[] */
    protected array $writers;

    public function setUp(): void
    {
        $this->writers = [
            $this->createMock(WriteInterface::class),
            $this->createMock(WriteInterface::class),
            $this->createMock(WriteInterface::class),
        ];

        $this->multiWrite = new MultiWrite(...$this->writers);
    }

    public function testGetWriters()
    {
        $this->assertSame($this->writers, $this->multiWrite->getWriters());
    }

    public function testGetStorage()
    {
        $storage = new \stdClass();

        $this->writers[0]->expects($this->once())->method('getStorage')->willReturn($storage);
        $this->writers[1]->expects($this->never())->method('getStorage');
        $this->writers[2]->expects($this->never())->method('getStorage');

        $this->assertSame($storage, $this->multiWrite->getStorage());
    }

    public function testWithLogging()
    {
        $writersWithLogging = [
            $this->createMock(WriteInterface::class),
            $this->createMock(WriteInterface::class),
            $this->createMock(WriteInterface::class),
        ];

        $logger = $this->createMock(LoggerInterface::class);

        foreach ($this->writers as $index => $writer) {
            $writer->expects($this->once())->method('withLogging')
                ->with($this->identicalTo($logger))
                ->willReturn($writersWithLogging[$index]);
        }

        $multiWriteWithLogging = $this->multiWrite->withLogging($logger);

        $this->assertInstanceOf(MultiWrite::class, $multiWriteWithLogging);
        $this->assertSame($writersWithLogging, $multiWriteWithLogging->getWriters());

        $this->assertNotSame($this->multiWrite, $multiWriteWithLogging);
        $this->assertSame($this->writers, $this->multiWrite->getWriters()); // Test immutability
    }

    public function testWithLoggingIdempotent()
    {
        $logger = $this->createMock(LoggerInterface::class);

        foreach ($this->writers as $index => $writer) {
            $writer->expects($this->once())->method('withLogging')
                ->with($this->identicalTo($logger))
                ->willReturnSelf();
        }

        $multiWriteWithLogging = $this->multiWrite->withLogging($logger);

        $this->assertSame($this->multiWrite, $multiWriteWithLogging);
    }

    public function testSave()
    {
        $opts = $this->createStubOpts();

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

        foreach ($this->writers as $index => $writer) {
            $writer->expects($this->once())->method('save')
                ->with(
                    $this->identicalTo($itemConsecutive[$index]),
                    $this->identicalTo($opts[0]),
                    $this->identicalTo($opts[1]),
                    $this->identicalTo($opts[2]),
                )
                ->willReturn($results[$index]);
        }

        $result = $this->multiWrite->save($itemConsecutive[0], ...$opts);

        $this->assertSame($results[2], $result);
    }

    public function testSaveAll()
    {
        $opts = $this->createStubOpts();

        $items = [new \stdClass(), new \stdClass()];

        $results = [
            $this->createMock(Result::class),
            $this->createMock(Result::class),
            $this->createMock(Result::class),
        ];

        foreach ($this->writers as $index => $writer) {
            $writer->expects($this->once())->method('saveAll')
                ->with(
                    $this->identicalTo($index === 0 ? $items : $results[$index - 1]),
                    $this->identicalTo($opts[0]),
                    $this->identicalTo($opts[1]),
                    $this->identicalTo($opts[2]),
                )
                ->willReturn($results[$index]);
        }

        $result = $this->multiWrite->saveAll($items, ...$opts);

        $this->assertSame($results[2], $result);
    }

    public function testAsssertAppyResultForSave()
    {
        $this->expectException(\BadMethodCallException::class);
        $this->multiWrite->save(new \stdClass());
    }

    public function testAsssertAppyResultForSaveAll()
    {
        $this->expectException(\BadMethodCallException::class);
        $this->multiWrite->saveAll([new \stdClass(), new \stdClass()]);
    }

    public function testUpdate()
    {
        $filter = [
            'foo' => 'bar',
            'color' => 'red',
        ];
        $instruction = $this->createMock(UpdateInstruction::class);
        $opts = $this->createStubOpts();

        $results = [
            $this->createMock(Result::class),
            $this->createMock(Result::class),
            $this->createMock(Result::class),
        ];

        foreach ($this->writers as $index => $writer) {
            $writer->expects($this->once())->method('update')
                ->with(
                    $filter,
                    $this->identicalTo($instruction),
                    $this->identicalTo($opts[0]),
                    $this->identicalTo($opts[1]),
                    $this->identicalTo($opts[2]),
                )
                ->willReturn($results[$index]);
        }

        $result = $this->multiWrite->update($filter, $instruction, ...$opts);

        $this->assertSame($results[0], $result);
    }

    public function testDelete()
    {
        $filter = [
            'foo' => 'bar',
            'color' => 'red',
        ];
        $opts = $this->createStubOpts();

        $results = [
            $this->createMock(Result::class),
            $this->createMock(Result::class),
            $this->createMock(Result::class),
        ];

        foreach ($this->writers as $index => $writer) {
            $writer->expects($this->once())->method('delete')
                ->with(
                    $filter,
                    $this->identicalTo($opts[0]),
                    $this->identicalTo($opts[1]),
                    $this->identicalTo($opts[2]),
                )
                ->willReturn($results[$index]);
        }

        $result = $this->multiWrite->delete($filter, ...$opts);

        $this->assertSame($results[0], $result);
    }

    /**
     * @return OptionInterface[]
     */
    protected function createStubOpts(): array
    {
        return [
            opts\apply_result(),
            $this->createStub(OptionInterface::class),
            $this->createStub(OptionInterface::class),
        ];
    }
}
