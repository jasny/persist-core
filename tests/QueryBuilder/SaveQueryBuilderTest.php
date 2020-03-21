<?php

declare(strict_types=1);

namespace Jasny\DB\Tests\QueryBuilder;

use Jasny\DB\Option\OptionInterface;
use Jasny\DB\QueryBuilder\SaveQueryBuilder;
use Jasny\PHPUnit\CallbackMockTrait;
use PHPUnit\Framework\MockObject\Builder\InvocationMocker;
use PHPUnit\Framework\TestCase;

/**
 * @covers \Jasny\DB\QueryBuilder\SaveQueryBuilder
 */
class SaveQueryBuilderTest extends TestCase
{
    use CallbackMockTrait;

    public function test()
    {
        $accumulator = (object)[];

        $items = [
            ['id' => 1, 'name' => 'one'],
            ['id' => 2, 'name' => 'two'],
            ['id' => 3, 'name' => 'three'],
        ];
        $preparedItems = [
            ['_id' => 1, 'name' => 'one'],
            ['_id' => 2, 'name' => 'two'],
            ['_id' => 3, 'name' => 'three'],
        ];

        $opts = [
            $this->createMock(OptionInterface::class),
            $this->createMock(OptionInterface::class),
        ];

        $prepare = $this->createCallbackMock($this->once(), [$items, $opts], $preparedItems);

        $compose = $this->createCallbackMock(
            $this->exactly(3),
            function (InvocationMocker $invoke) use ($accumulator, $preparedItems, $opts) {
                $invoke->withConsecutive(
                    [$this->identicalTo($accumulator), $preparedItems[0], 0, $opts],
                    [$this->identicalTo($accumulator), $preparedItems[1], 1, $opts],
                    [$this->identicalTo($accumulator), $preparedItems[2], 2, $opts],
                );
            },
        );

        $finalize = $this->createCallbackMock($this->once(), [$this->identicalTo($accumulator), $opts]);

        $builder = (new SaveQueryBuilder($compose))
            ->withPreparation($prepare)
            ->withFinalization($finalize);

        $builder->apply($accumulator, $items, $opts);
    }
}
