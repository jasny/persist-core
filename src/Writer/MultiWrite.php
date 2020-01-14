<?php

declare(strict_types=1);

namespace Jasny\DB\Writer;

use Improved\IteratorPipeline\Pipeline;
use Jasny\DB\Update\UpdateInstruction;
use Jasny\Immutable;
use Jasny\DB\Option as opts;
use Jasny\DB\Option\OptionInterface;
use Jasny\DB\Result\Result;
use Psr\Log\LoggerInterface;

/**
 * Write to multiple storage endpoints (sequentially).
 * @immutable
 *
 * @template TItem
 * @implements WriteInterface<TItem>
 */
class MultiWrite implements WriteInterface
{
    use Immutable\With;

    /** @var array<WriteInterface<TItem>> */
    protected array $writers = [];

    /**
     * MultiWrite constructor.
     *
     * @param WriteInterface<TItem> $main
     * @param WriteInterface<TItem> ...$additional
     */
    public function __construct(WriteInterface $main, WriteInterface ...$additional)
    {
        $this->writers = array_merge([$main], $additional);
    }

    /**
     * Get underlying writers.
     *
     * @return array<WriteInterface<TItem>>
     */
    public function getWriters(): array
    {
        return $this->writers;
    }

    /**
     * Get underlying storage object of the main writer.
     *
     * @return mixed
     */
    public function getStorage()
    {
        return $this->writers[0]->getStorage();
    }

    /**
     * Enable logging for each underlying writer.
     *
     * @param LoggerInterface $logger
     * @return static
     */
    public function withLogging(LoggerInterface $logger)
    {
        $writers = Pipeline::with($this->writers)
            ->map(fn(WriteInterface $writer) => $writer->withLogging($logger))
            ->toArray();

        return $this->withProperty('writers', $writers);
    }


    /**
     * Save the one item.
     * The use of the `apply_result` option is required.
     *
     * @param TItem             $item
     * @param OptionInterface[] $opts
     * @return Result<TItem>
     */
    public function save($item, array $opts = []): Result
    {
        $this->assertApplyResult($opts);

        return $this->saveEach(static function (WriteInterface $writer, ?Result $result) use ($item, $opts): Result {
            return $writer->save($result === null ? $item : $result->first(), $opts);
        });
    }

    /**
     * Save the multiple items.
     * The use of the `apply_result` option is required.
     *
     * @param iterable<TItem>   $items
     * @param OptionInterface[] $opts
     * @return Result<TItem>
     */
    public function saveAll(iterable $items, array $opts = []): Result
    {
        $this->assertApplyResult($opts);

        return $this->saveEach(static function (WriteInterface $writer, ?Result $result) use ($items, $opts): Result {
            return $writer->saveAll($result ?? $items, $opts);
        });
    }

    /**
     * Save one or multiple items to each storage.
     *
     * @return Result<TItem>
     */
    protected function saveEach(callable $fn): Result
    {
        $result = null;

        foreach ($this->writers as $writer) {
            $result = $fn($writer, $result);
        }

        if ($result === null) {
            throw new \LogicException("No writers were called"); // @codeCoverageIgnore
        }

        return $result;
    }

    /**
     * Query and update records.
     * Returns the result of the main storage.
     *
     * @param array<string,mixed>                   $filter
     * @param UpdateInstruction|UpdateInstruction[] $instructions
     * @param OptionInterface[]                     $opts
     * @return Result<TItem|\stdClass>
     */
    public function update(array $filter, $instructions, array $opts = []): Result
    {
        $result = Pipeline::with($this->writers)
            ->map(fn(WriteInterface $writer) => $writer->update($filter, $instructions, $opts))
            ->toArray();

        return $result[0];
    }

    /**
     * Query and delete records.
     * Returns the result of the main storage.
     *
     * @param array<string, mixed> $filter
     * @param OptionInterface[]    $opts
     * @return Result<TItem|\stdClass>
     */
    public function delete(array $filter, array $opts = []): Result
    {
        $result = Pipeline::with($this->writers)
            ->map(fn(WriteInterface $writer) => $writer->delete($filter, $opts))
            ->toArray();

        return $result[0];
    }

    /**
     * Assert that apply_result option is used.
     *
     * @param OptionInterface[] $opts
     */
    protected function assertApplyResult(array $opts): void
    {
        if (!opts\apply_result()->isIn($opts)) {
            throw new \BadMethodCallException("The `apply_result` option is required when using multi write");
        }
    }
}
