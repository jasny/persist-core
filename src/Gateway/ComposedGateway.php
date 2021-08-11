<?php

declare(strict_types=1);

namespace Jasny\Persist\Gateway;

use Jasny\Persist\Option\OptionInterface;
use Jasny\Persist\Result\Result;
use Jasny\Immutable;
use Psr\Log\LoggerInterface;

/**
 * Gateway composed of different gateways for read, update, save, or delete.
 *
 * @template TItem
 * @implements GatewayInterface<TItem>
 */
class ComposedGateway implements GatewayInterface
{
    use Immutable\With;

    private GatewayInterface $read;
    private GatewayInterface $save;
    private GatewayInterface $update;
    private GatewayInterface $delete;

    /**
     * MultiWrite constructor.
     *
     * @param GatewayInterface<TItem>|GatewayInterface<null> $read
     * @param GatewayInterface<TItem>|GatewayInterface<null> $update
     * @param GatewayInterface<TItem>|GatewayInterface<null> $save
     * @param GatewayInterface<TItem>|GatewayInterface<null> $delete
     */
    public function __construct(
        GatewayInterface $read,
        GatewayInterface $save,
        GatewayInterface $update,
        GatewayInterface $delete
    ) {
        $this->read = $read;
        $this->update = $update;
        $this->save = $save;
        $this->delete = $delete;
    }


    /**
     * @inheritDoc
     */
    public function withLogging(LoggerInterface $logger): static
    {
        return $this
            ->withProperty('read', $this->read->withLogging($logger))
            ->withProperty('save', $this->save->withLogging($logger))
            ->withProperty('update', $this->update->withLogging($logger))
            ->withProperty('delete', $this->delete->withLogging($logger));
    }

    /**
     * @inheritDoc
     */
    public function fetch(array $filter = [], OptionInterface ...$opts): Result
    {
        return $this->read->fetch($filter, ...$opts);
    }

    /**
     * @inheritDoc
     */
    public function count(array $filter = [], OptionInterface ...$opts): int
    {
        return $this->read->count($filter, ...$opts);
    }

    /**
     * @inheritDoc
     */
    public function save(array|object $item, OptionInterface ...$opts): Result
    {
        return $this->save->save($item, ...$opts);
    }

    /**
     * @inheritDoc
     */
    public function saveAll(iterable $items, OptionInterface ...$opts): Result
    {
        return $this->save->saveAll($items, ...$opts);
    }

    /**
     * @inheritDoc
     */
    public function update(array $filter, $instructions, OptionInterface ...$opts): Result
    {
        return $this->update->update($filter, $instructions, ...$opts);
    }

    /**
     * @inheritDoc
     */
    public function delete(array $filter, OptionInterface ...$opts): Result
    {
        return $this->delete->delete($filter, ...$opts);
    }
}
