<?php

declare(strict_types=1);

namespace Jasny\Persist\Query;

use Improved\IteratorPipeline\Pipeline;

/**
 * Composite class for query composers.
 *
 * @template TQuery
 * @implements ComposerInterface<TQuery,mixed,mixed>
 */
final class Composer implements ComposerInterface
{
    /**
     * @var array<ComposerInterface<TQuery,mixed,mixed>>
     */
    protected array $steps = [];

    /**
     * @param ComposerInterface<TQuery,mixed,mixed> ...$steps
     */
    public function __construct(ComposerInterface ...$steps)
    {
        $this->steps = Pipeline::with($steps)
            ->map(fn(ComposerInterface $step) => $step instanceof self ? $step->steps : $step)
            ->flatten()
            ->sort(fn(ComposerInterface $step) => $step->getPriority())
            ->toArray();
    }

    /**
     * @inheritDoc
     */
    public function getPriority(): int
    {
        return isset($this->steps[0]) ? $this->steps[0]->getPriority() : PHP_INT_MAX;
    }

    /**
     * @inheritDoc
     */
    public function compose(object $accumulator, iterable $items, array &$opts = []): iterable
    {
        foreach ($this->steps as $step) {
            $items = $step->compose($accumulator, $items, $opts);
        }

        return $items;
    }
}
