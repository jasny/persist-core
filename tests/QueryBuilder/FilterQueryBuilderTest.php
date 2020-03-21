<?php

declare(strict_types=1);

namespace Jasny\DB\Tests\QueryBuilder;

use Jasny\DB\Filter\FilterItem;
use Jasny\DB\Option\OptionInterface;
use Jasny\DB\QueryBuilder\FilterQueryBuilder;
use Jasny\PHPUnit\CallbackMockTrait;
use PHPUnit\Framework\MockObject\Builder\InvocationMocker;
use PHPUnit\Framework\TestCase;

/**
 * @covers \Jasny\DB\QueryBuilder\AbstractQueryBuilder
 * @covers \Jasny\DB\QueryBuilder\FilterQueryBuilder
 */
class FilterQueryBuilderTest extends TestCase
{
    use CallbackMockTrait;

    protected object $acc;
    protected array $opts;

    public function setUp(): void
    {
        $this->acc = (object)[];
        $this->opts = [
            $this->createMock(OptionInterface::class),
            $this->createMock(OptionInterface::class),
        ];
    }

    public function testApplyWithDefaults()
    {
        $invocation = function (InvocationMocker $invoke) {
            $invoke->withConsecutive(
                [$this->identicalTo($this->acc), new FilterItem('foo', '', 1), $this->identicalTo($this->opts)],
                [$this->identicalTo($this->acc), new FilterItem('bar', 'min', 42), $this->identicalTo($this->opts)],
                [$this->identicalTo($this->acc), new FilterItem('qux', '', [1, 2]), $this->identicalTo($this->opts)],
            );
        };
        $compose = $this->createCallbackMock($this->exactly(3), $invocation);

        $builder = new FilterQueryBuilder($compose);
        $builder->apply($this->acc, ['foo' => 1, 'bar(min)' => 42, 'qux' => [1, 2]], $this->opts);
    }

    public function testCustomParser()
    {
        $filterItems = [
            $this->createConfiguredMock(FilterItem::class, ['getField' => 'foo','getOperator' => '']),
            $this->createConfiguredMock(FilterItem::class, ['getField' => 'bar','getOperator' => 'min']),
            $this->createConfiguredMock(FilterItem::class, ['getField' => 'qux','getOperator' => '']),
        ];

        $invocation = function (InvocationMocker $invoke) use ($filterItems) {
            $invoke->withConsecutive(
                [$this->identicalTo($this->acc), $this->identicalTo($filterItems[0]), $this->identicalTo($this->opts)],
                [$this->identicalTo($this->acc), $this->identicalTo($filterItems[1]), $this->identicalTo($this->opts)],
                [$this->identicalTo($this->acc), $this->identicalTo($filterItems[2]), $this->identicalTo($this->opts)],
            );
        };
        $compose = $this->createCallbackMock($this->exactly(3), $invocation);

        $filter = ['foo' => 1, 'bar:min' => 42, 'qux[0]' => 1, 'qux[2]' => 1];
        $parser = $this->createCallbackMock($this->once(), [$filter], $filterItems);

        $builder = new FilterQueryBuilder($compose, $parser);
        $builder->apply($this->acc, $filter, $this->opts);
    }

    public function testPreparation()
    {
        $invocation = function (InvocationMocker $invoke) {
            $invoke->withConsecutive(
                [$this->identicalTo($this->acc), new FilterItem('BAR', 'min', 42), $this->identicalTo($this->opts)],
                [$this->identicalTo($this->acc), new FilterItem('QUX', '', [1, 2]), $this->identicalTo($this->opts)],
            );
        };
        $compose = $this->createCallbackMock($this->exactly(2), $invocation);

        $prepare = $this->createCallbackMock(
            $this->once(),
            [
                [
                    new FilterItem('foo', '', 1),
                    new FilterItem('bar', 'min', 42),
                    new FilterItem('qux', '', [1, 2]),
                ],
                $this->identicalTo($this->opts)
            ],
            [
                new FilterItem('BAR', 'min', 42),
                new FilterItem('QUX', '', [1, 2])
            ]
        );

        $base = new FilterQueryBuilder($compose);

        $builder = $base->withPreparation($prepare);
        $this->assertInstanceOf(FilterQueryBuilder::class, $builder);
        $this->assertNotSame($base, $builder);
        $this->assertSame($prepare, $builder->getPreparation());

        $builder->apply($this->acc, ['foo' => 1, 'bar(min)' => 42, 'qux' => [1, 2]], $this->opts);
    }
    
    public function testPreparationMultiple()
    {
        $filter1 = [new FilterItem('one', '', 1)];
        $filter2 = [new FilterItem('two', '', 2)];
        $filter3 = [new FilterItem('three', '', 3)];

        $compose = $this->createCallbackMock(
            $this->once(),
            [$this->identicalTo($this->acc), $this->identicalTo($filter3[0]), $this->opts],
        );

        $prepare1 = $this->createCallbackMock(
            $this->once(),
            [$this->identicalTo($filter1), $this->opts],
            $filter2,
        );
        $prepare2 = $this->createCallbackMock(
            $this->once(),
            [$this->identicalTo($filter2), $this->opts],
            $filter3,
        );

        $builder = (new FilterQueryBuilder($compose))
            ->withPreparation($prepare1, $prepare2);

        $builder->apply($this->acc, $filter1, $this->opts);
    }

    public function testFinalization()
    {
        $compose = $this->createCallbackMock($this->exactly(3), []);

        $finalize = $this->createCallbackMock(
            $this->once(),
            [$this->identicalTo($this->acc), $this->identicalTo($this->opts)]
        );

        $builder = (new FilterQueryBuilder($compose))->withFinalization($finalize);
        $builder->apply($this->acc, ['foo' => 1, 'bar(min)' => 42, 'qux' => [1, 2]], $this->opts);
    }

    public function testFinalizationMultiple()
    {
        $filter = [new FilterItem('one', '', 1)];

        $compose = $this->createCallbackMock(
            $this->once(),
            [$this->identicalTo($this->acc), $this->identicalTo($filter[0]), $this->opts],
        );

        $finalize1 = $this->createCallbackMock($this->once(), [$this->identicalTo($this->acc), $this->opts]);
        $finalize2 = $this->createCallbackMock($this->once(), [$this->identicalTo($this->acc), $this->opts]);

        $builder = (new FilterQueryBuilder($compose))
            ->withFinalization($finalize1, $finalize2);

        $builder->apply($this->acc, $filter, $this->opts);
    }


    public function testWithCustomFilter()
    {
        $compose = $this->createCallbackMock(
            $this->once(),
            [$this->identicalTo($this->acc), new FilterItem('qux', '', [1, 2]), $this->identicalTo($this->opts)]
        );

        $customFoo = $this->createCallbackMock(
            $this->once(),
            [$this->identicalTo($this->acc), new FilterItem('foo', '', 1), $this->identicalTo($this->opts)]
        );

        $customBar = $this->createCallbackMock(
            $this->once(),
            [$this->identicalTo($this->acc), new FilterItem('bar', 'min', 42), $this->identicalTo($this->opts)]
        );

        $builder = (new FilterQueryBuilder($compose))
            ->withCustomFilter('foo', $customFoo)
            ->withCustomFilter('bar', $customBar);

        $builder->apply($this->acc, ['foo' => 1, 'bar(min)' => 42, 'qux' => [1, 2]], $this->opts);
    }

    public function testWithoutCustomFilter()
    {
        $invocation = function (InvocationMocker $invoke) {
            $invoke->withConsecutive(
                [$this->identicalTo($this->acc), new FilterItem('foo', '', 1), $this->identicalTo($this->opts)],
                [$this->identicalTo($this->acc), new FilterItem('qux', '', [1, 2]), $this->identicalTo($this->opts)]
            );
        };
        $compose = $this->createCallbackMock($this->exactly(2), $invocation);

        $customFoo = $this->createCallbackMock($this->never());

        $customBar = $this->createCallbackMock(
            $this->once(),
            [$this->identicalTo($this->acc), new FilterItem('bar', 'min', 42), $this->identicalTo($this->opts)]
        );

        $builder = (new FilterQueryBuilder($compose))
            ->withCustomFilter('foo', $customFoo)
            ->withCustomFilter('bar', $customBar)
            ->withoutCustomFilter('foo');

        $builder->apply($this->acc, ['foo' => 1, 'bar(min)' => 42, 'qux' => [1, 2]], $this->opts);
    }

    public function testWithCustomFilterOperator()
    {
        $compose = $this->createCallbackMock(
            $this->once(),
            [$this->identicalTo($this->acc), new FilterItem('foo', '', 1), $this->identicalTo($this->opts)]
        );

        $customTop = $this->createCallbackMock(
            $this->once(),
            [$this->identicalTo($this->acc), new FilterItem('bar', 'top', 42), $this->identicalTo($this->opts)]
        );

        $customBetween = $this->createCallbackMock(
            $this->once(),
            [$this->identicalTo($this->acc), new FilterItem('qux', '<>', [1, 2]), $this->identicalTo($this->opts)]
        );

        $builder = (new FilterQueryBuilder($compose))
            ->withCustomOperator('top', $customTop)
            ->withCustomOperator('<>', $customBetween);

        $builder->apply($this->acc, ['foo' => 1, 'bar(top)' => 42, 'qux (<>)' => [1, 2]], $this->opts);
    }

    public function testWithoutCustomFilterOperator()
    {
        $invocation = function (InvocationMocker $invoke) {
            $invoke->withConsecutive(
                [$this->identicalTo($this->acc), new FilterItem('foo', '', 1), $this->identicalTo($this->opts)],
                [$this->identicalTo($this->acc), new FilterItem('qux', '<>', [1, 2]), $this->identicalTo($this->opts)]
            );
        };
        $compose = $this->createCallbackMock($this->exactly(2), $invocation);

        $customTop = $this->createCallbackMock(
            $this->once(),
            [$this->identicalTo($this->acc), new FilterItem('bar', 'top', 42), $this->identicalTo($this->opts)]
        );

        $customBetween = $this->createCallbackMock($this->never());

        $builder = (new FilterQueryBuilder($compose))
            ->withCustomOperator('top', $customTop)
            ->withCustomOperator('<>', $customBetween)
            ->withoutCustomOperator('<>');

        $builder->apply($this->acc, ['foo' => 1, 'bar(top)' => 42, 'qux (<>)' => [1, 2]], $this->opts);
    }

    /**
     * {@internal Calling next is difficult with mocked callbacks, so not mocking.}}
     */
    public function testWithCustomFilterCombo()
    {
        $compose = function ($acc, $item, $opts) {
            $this->assertSame($this->acc, $acc);
            $this->assertEquals(new FilterItem('bar', 'max', 100), $item);
            $this->assertSame($this->opts, $opts);

            $acc->default = true;
        };

        $customBar = function ($acc, $item, $opts, $next) {
            $this->assertSame($this->acc, $acc);
            $this->assertEquals(new FilterItem('bar', 'top', 1), $item);
            $this->assertSame($this->opts, $opts);
            $this->assertIsCallable($next);

            $acc->bar = true;
            $next(new FilterItem('bar', 'top', 100));
        };

        $customTop = function ($acc, $item, $opts, $next) {
            $this->assertSame($this->acc, $acc);
            $this->assertEquals(new FilterItem('bar', 'top', 100), $item);
            $this->assertSame($this->opts, $opts);
            $this->assertIsCallable($next);

            $acc->top = true;
            $next(new FilterItem('bar', 'max', 100));
        };

        $builder = (new FilterQueryBuilder($compose))
            ->withCustomFilter('bar', $customBar)
            ->withCustomOperator('top', $customTop);

        $builder->apply($this->acc, ['bar(top)' => 1], $this->opts);

        $this->assertEquals(['bar' => true, 'top' => true, 'default' => true], (array)$this->acc);
    }
}
