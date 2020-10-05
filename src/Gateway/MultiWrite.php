<?php

declare(strict_types=1);

namespace Persist\Gateway;

use Jasny\Immutable;
use Persist\Option\OptionInterface;
use Persist\Result\Result;
use Psr\Log\LoggerInterface;

/**
 * Gateway to write multiple storage endpoints (sequentially).
 *
 * @template TItem
 * @implements GatewayInterface<TItem>
 */
class MultiWrite implements GatewayInterface
{
    use Immutable\With;

    /**
     * @var GatewayInterface[]
     * @phpstan-var array<GatewayInterface<TItem>>
     */
    protected array $inner = [];

    /**
     * MultiWrite constructor.
     *
     * @phpstan-param GatewayInterface<TItem> $main
     * @phpstan-param GatewayInterface<TItem> ...$additional
     */
    public function __construct(GatewayInterface $main, GatewayInterface ...$additional)
    {
        $this->inner = array_merge([$main], $additional);
    }

    /**
     * Get underlying inner.
     *
     * @return GatewayInterface[]
     *
     * @phpstan-return array<GatewayInterface<TItem>>
     */
    public function getInner(): array
    {
        return $this->inner;
    }

    /**
     * Enable logging for each underlying writer.
     *
     * @param LoggerInterface $logger
     * @return static
     */
    public function withLogging(LoggerInterface $logger)
    {
        $inner = [];

        foreach ($this->inner as $gateway) {
            $inner[] = $gateway->withLogging($logger);
        }

        return $this->withProperty('inner', $inner);
    }


    /**
     * @inheritDoc
     */
    public function fetch(array $filter = [], OptionInterface ...$opts): Result
    {
        return $this->inner[0]->fetch($filter, ...$opts);
    }

    /**
     * @inheritDoc
     */
    public function count(array $filter = [], OptionInterface ...$opts): int
    {
        return $this->inner[0]->count($filter, ...$opts);
    }

    /**
     * @inheritDoc
     */
    public function save($item, OptionInterface ...$opts): Result
    {
        $result = new Result([$item]);

        foreach ($this->inner as $gateway) {
            $result = $gateway->save($result->first(), ...$opts);
        }

        return $result;
    }

    /**
     * @inheritDoc
     */
    public function saveAll(iterable $items, OptionInterface ...$opts): Result
    {
        $result = new Result($items);

        foreach ($this->inner as $gateway) {
            $result = $gateway->saveAll($result, ...$opts);
        }

        return $result;
    }

    /**
     * @inheritDoc
     */
    public function update(array $filter, $instructions, OptionInterface ...$opts): Result
    {
        $result = null;

        foreach ($this->inner as $gateway) {
            $curResult = $gateway->update($filter, $instructions, ...$opts);
            $result ??= $curResult;
        }

        return $result ?? new Result();
    }

    /**
     * @inheritDoc
     */
    public function delete(array $filter, OptionInterface ...$opts): Result
    {
        $result = null;

        foreach ($this->inner as $gateway) {
            $curResult = $gateway->delete($filter, ...$opts);
            $result ??= $curResult;
        }

        return $result ?? new Result();
    }
}
