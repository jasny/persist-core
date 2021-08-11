<?php

declare(strict_types=1);

namespace Jasny\Persist\Gateway;

use Jasny\Persist\Option\OptionInterface;
use Jasny\Persist\Result\Result;
use Jasny\Persist\Update\UpdateInstruction;
use Psr\Log\LoggerInterface;

/**
 * Gateway without a storage. Data isn't saved.
 *
 * @implements GatewayInterface<null>
 */
final class Blackhole implements GatewayInterface
{
    /**
     * Does nothing.
     *
     * @return $this
     */
    public function withLogging(LoggerInterface $logger): self
    {
        return $this;
    }

    /**
     * @inheritDoc
     */
    public function fetch(array $filter = [], OptionInterface ...$opts): Result
    {
        return new Result();
    }

    /**
     * @inheritDoc
     */
    public function count(array $filter = [], OptionInterface ...$opts): int
    {
        return 0;
    }

    /**
     * @inheritDoc
     */
    public function update(array $filter, UpdateInstruction|array $instructions, OptionInterface ...$opts): Result
    {
        return new Result();
    }

    /**
     * @inheritDoc
     */
    public function save(array|object $item, OptionInterface ...$opts): Result
    {
        return new Result([$item]);
    }

    /**
     * @inheritDoc
     */
    public function saveAll(iterable $items, OptionInterface ...$opts): Result
    {
        return new Result($items);
    }

    /**
     * @inheritDoc
     */
    public function delete(array $filter, OptionInterface ...$opts): Result
    {
        return new Result();
    }
}
