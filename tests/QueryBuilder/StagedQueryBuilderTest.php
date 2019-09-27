<?php

declare(strict_types=1);

namespace Jasny\DB\Tests\QueryBuilder;

use Improved as i;
use Jasny\DB\Exception\BuildQueryException;
use Jasny\DB\Option\OptionInterface;
use Jasny\DB\QueryBuilder\FilterQueryBuilder;
use Jasny\TestHelper;
use PHPUnit\Framework\Constraint\Exception as ExceptionConstraint;
use PHPUnit\Framework\Constraint\ExceptionMessage;
use PHPUnit\Framework\MockObject\Builder\InvocationMocker;
use PHPUnit\Framework\TestCase;

/**
 * @covers \Jasny\DB\QueryBuilder\FilterQueryBuilder
 */
class StagedQueryBuilderTest extends TestCase
{
    use TestHelper;

    public function abProvider()
    {
        return [
            'apply()' => ['method'],
            '__invoke()' => ['invoke'],
        ];
    }

    /**
     * @dataProvider abProvider
     * Testing all 'on*' methods at once. It's important that they're executed in the correct order.
     */
    public function testBuild($ab)
    {
        $accumulator = (object)[];

        $opts = [
            $this->createMock(OptionInterface::class),
            $this->createMock(OptionInterface::class)
        ];

        $composeCallbacks = [fn() => 1, fn() => 2];

        $builder = (new FilterQueryBuilder())
            ->onPrepare($this->createCallbackMock( /* 1 */
                $this->once(),
                [['foo' => 1, 'bar' => 10], $opts],
                ['foo' => 2, 'bar' => 10]
            ))
            ->onCompose($this->createCallbackMock( /* 3 */
                $this->once(),
                [['foo' => 22, 'color' => 'red', 'shape' => 'square'], $opts],
                $composeCallbacks
            ))
            ->onFinalize($this->createCallbackMock( /* 5 */
                $this->once(),
                [$this->identicalTo($accumulator), $opts],
            ))
            ->onBuild($this->createCallbackMock( /* 4 */
                $this->once(),
                function (InvocationMocker $invoke) use ($accumulator, $composeCallbacks, $opts) {
                    $assertIterable = $this->callback(function ($arg) use ($composeCallbacks) {
                        return is_iterable($arg) && i\iterable_to_array($arg) === $composeCallbacks;
                    });

                    $invoke->with($this->identicalTo($accumulator), $assertIterable, $opts);
                }
            ))
            ->onPrepare($this->createCallbackMock( /* 2 */
                $this->once(),
                [['foo' => 2, 'bar' => 10], $opts],
                ['foo' => 22, 'color' => 'red', 'shape' => 'square']
            ))
        ;

        if ($ab === 'method') {
            $builder->apply($accumulator, ['foo' => 1, 'bar' => 10], $opts);
        } else {
            $builder($accumulator, ['foo' => 1, 'bar' => 10], $opts);
        }
    }

    public function testBuildWithRequiredSteps()
    {
        $accumulator = (object)[];

        $opts = [
            $this->createMock(OptionInterface::class),
            $this->createMock(OptionInterface::class)
        ];

        $composeCallbacks = [fn() => 1, fn() => 2];

        $builder = (new FilterQueryBuilder())
            ->onCompose($this->createCallbackMock(
                $this->once(),
                [['foo' => 1, 'bar' => 10], $opts],
                $composeCallbacks
            ))
            ->onBuild($this->createCallbackMock( /* 4 */
                $this->once(),
                function (InvocationMocker $invoke) use ($accumulator, $composeCallbacks, $opts) {
                    $assertIterable = $this->callback(function ($arg) use ($composeCallbacks) {
                        return is_iterable($arg) && i\iterable_to_array($arg) === $composeCallbacks;
                    });

                    $invoke->with($this->identicalTo($accumulator), $assertIterable, $opts);
                }
            ))
        ;

        $builder->apply($accumulator, ['foo' => 1, 'bar' => 10], $opts);
    }

    public function testWithoutSteps()
    {
        $builder = (new FilterQueryBuilder())
            ->onPrepare($this->createCallbackMock($this->never()))
            ->onPrepare($this->createCallbackMock($this->never()))
            ->onCompose($this->createCallbackMock($this->never()))
            ->onCompose($this->createCallbackMock($this->never()))
            ->onBuild($this->createCallbackMock($this->never()))
            ->onFinalize($this->createCallbackMock($this->never()))
            ->withoutPrepare()
            ->withoutCompose()
            ->onPrepare($this->createCallbackMock($this->once(), [], []))
            ->onCompose($this->createCallbackMock($this->once(), [], []))
            ->withoutFinalize()
            ->withoutBuild()
            ->onBuild($this->createCallbackMock($this->once(), [], []))
            ->onCompose($this->createCallbackMock($this->once(), [], []))
            ->onFinalize($this->createCallbackMock($this->once(), [], []))
        ;

        $builder->apply((object)[], []);
    }

    public function testWithoutSpecificSteps()
    {
        $never = $this->createCallbackMock($this->never());

        $builder = (new FilterQueryBuilder())
            ->onPrepare($never)
            ->onPrepare($this->createCallbackMock($this->once(), [], []))
            ->onCompose($never)
            ->onCompose($never)
            ->onBuild($this->createCallbackMock($this->once(), [], []))
            ->onFinalize($this->createCallbackMock($this->once(), [], []))
            ->onFinalize($never)
            ->withoutPrepare(fn($step) => ($step === $never))
            ->withoutCompose(fn($step) => ($step === $never))
            ->onPrepare($this->createCallbackMock($this->once(), [], []))
            ->onCompose($this->createCallbackMock($this->once(), [], []))
            ->withoutFinalize(fn($step) => ($step === $never))
            ->onFinalize($this->createCallbackMock($this->once(), [], []))
        ;

        $builder->apply((object)[], []);
    }

    public function testPrepareException()
    {
        $exception = new \RuntimeException("something's wrong");
        $callback = $this->createCallbackMock($this->once(), function (InvocationMocker $invoke) use ($exception) {
            $invoke->willThrowException($exception);
        });

        $buildException = new BuildQueryException(
            sprintf("Query builder failed in prepare step 2 of 3 (instance of %s)", get_class($callback)),
            0,
            $exception
        );

        $builder = (new FilterQueryBuilder())
            ->onPrepare($this->createCallbackMock($this->once(), [], []))
            ->onPrepare($callback)
            ->onPrepare($this->createCallbackMock($this->never()))
            ->onCompose($this->createCallbackMock($this->never()))
            ->onBuild($this->createCallbackMock($this->never()))
            ->onFinalize($this->createCallbackMock($this->never()))
        ;

        $this->tryExpect(fn() => $builder->apply((object)[], []), $buildException);
    }

    public function testComposeException()
    {
        $exception = new \RuntimeException("something's wrong");
        $callback = $this->createCallbackMock($this->once(), function (InvocationMocker $invoke) use ($exception) {
            $invoke->willThrowException($exception);
        });

        $buildException = new BuildQueryException(
            sprintf("Query builder failed in compose step 1 of 2 (instance of %s)", get_class($callback)),
            0,
            $exception
        );

        $builder = (new FilterQueryBuilder())
            ->onPrepare($this->createCallbackMock($this->once(), [], []))
            ->onCompose($callback)
            ->onCompose($this->createCallbackMock($this->never()))
            ->onBuild($this->createCallbackMock($this->never()))
            ->onFinalize($this->createCallbackMock($this->never()))
        ;

        $this->tryExpect(fn() => $builder->apply((object)[], []), $buildException);
    }

    public function testBuildException()
    {
        $exception = new \RuntimeException("something's wrong");
        $callback = $this->createCallbackMock($this->once(), function (InvocationMocker $invoke) use ($exception) {
            $invoke->willThrowException($exception);
        });

        $buildException = new BuildQueryException(
            sprintf("Query builder failed in build step (instance of %s)", get_class($callback)),
            0,
            $exception
        );

        $builder = (new FilterQueryBuilder())
            ->onPrepare($this->createCallbackMock($this->once(), [], []))
            ->onCompose($this->createCallbackMock($this->once(), [], []))
            ->onBuild($callback)
            ->onFinalize($this->createCallbackMock($this->never()))
        ;

        $this->tryExpect(fn() => $builder->apply((object)[], []), $buildException);
    }

    public function testFinalizeException()
    {
        $exception = new \RuntimeException("something's wrong");
        $callback = $this->createCallbackMock($this->once(), function (InvocationMocker $invoke) use ($exception) {
            $invoke->willThrowException($exception);
        });

        $buildException = new BuildQueryException(
            sprintf("Query builder failed in finalize step 1 of 2 (instance of %s)", get_class($callback)),
            0,
            $exception
        );

        $builder = (new FilterQueryBuilder())
            ->onPrepare($this->createCallbackMock($this->once(), [], []))
            ->onCompose($this->createCallbackMock($this->once(), [], []))
            ->onBuild($this->createCallbackMock($this->once(), []))
            ->onFinalize($callback)
            ->onFinalize($this->createCallbackMock($this->never()))
        ;

        $this->tryExpect(fn() => $builder->apply((object)[], []), $buildException);
    }

    public function testImmutability()
    {
        $prepare = fn() => [];
        $compose = fn() => [];
        $build = fn() => [];
        $finalize = fn() => null;

        $empty = new FilterQueryBuilder();
        $base = $empty
            ->onPrepare($prepare)
            ->onCompose($compose)
            ->onBuild($build)
            ->onFinalize($finalize)
        ;

        $this->assertNotSame($empty, $base);

        $this->assertNotSame($empty, $empty->onPrepare(fn() => []));
        $this->assertNotSame($empty, $empty->onCompose(fn() => []));
        $this->assertNotSame($empty, $empty->onBuild(fn() => []));
        $this->assertNotSame($empty, $empty->onBuild(fn() => []));

        $this->assertNotSame($base, $base->onPrepare(fn() => []));
        $this->assertNotSame($base, $base->onCompose(fn() => []));
        $this->assertNotSame($base, $base->onFinalize(fn() => []));

        $this->assertNotSame($base, $base->withoutPrepare());
        $this->assertNotSame($base, $base->withoutCompose());
        $this->assertNotSame($base, $base->withoutBuild());
        $this->assertNotSame($base, $base->withoutFinalize());

        $this->assertNotSame($base, $base->withoutPrepare(fn() => true));
        $this->assertNotSame($base, $base->withoutCompose(fn() => true));
        $this->assertNotSame($base, $base->withoutFinalize(fn() => true));

        $this->assertSame($base, $base->withoutPrepare(fn() => false));
        $this->assertSame($base, $base->withoutCompose(fn() => false));
        $this->assertSame($base, $base->withoutFinalize(fn() => false));

        $this->assertSame($empty, $empty->withoutPrepare());
        $this->assertSame($empty, $empty->withoutCompose());
        $this->assertSame($empty, $empty->withoutBuild());
        $this->assertSame($empty, $empty->withoutFinalize());
    }


    public function testPrepareCheckIterable()
    {
        $buildException = new BuildQueryException(
            sprintf("Query builder failed in prepare step 1 of 1"),
            0,
            new \UnexpectedValueException('Expected iterable, string(5) "hello" returned')
        );

        $builder = (new FilterQueryBuilder())
            ->onPrepare($this->createCallbackMock($this->once(), function (InvocationMocker $invoke) {
                $invoke->willReturn('hello');
            }))
            ->onCompose($this->createCallbackMock($this->never()))
            ->onBuild($this->createCallbackMock($this->never()))
        ;

        $this->tryExpect(fn() => $builder->apply((object)[], []), $buildException);
    }

    public function testComposeCheckIterable()
    {
        $buildException = new BuildQueryException(
            sprintf("Query builder failed in compose step 1 of 1"),
            0,
            new \UnexpectedValueException('Expected iterable, string(5) "hello" returned')
        );

        $builder = (new FilterQueryBuilder())
            ->onPrepare($this->createCallbackMock($this->once(), [], []))
            ->onCompose($this->createCallbackMock($this->once(), function (InvocationMocker $invoke) {
                $invoke->willReturn('hello');
            }))
            ->onBuild($this->createCallbackMock($this->never()))
        ;

        $this->tryExpect(fn() => $builder->apply((object)[], []), $buildException);
    }

    public function testComposeCheckCallables()
    {
        $callback = $this->createCallbackMock($this->once(), function (InvocationMocker $invoke) {
            $invoke->willReturn(['not_a_callable']);
        });

        $buildException = new BuildQueryException(
            sprintf("Query builder failed in build step"),
            0,
            new \UnexpectedValueException(sprintf('Not all items created in compose step 1 (instance of %s) are'
                . ' callable, got string(14) "not_a_callable"', get_class($callback)))
        );

        $builder = (new FilterQueryBuilder())
            ->onPrepare($this->createCallbackMock($this->once(), [], []))
            ->onCompose($callback)
            ->onBuild(fn(object $accumulator, iterable $compose) => i\iterable_to_array($compose))
        ;

        $this->tryExpect(fn() => $builder->apply((object)[], []), $buildException);
    }


    public function testOnlyOneBuildStep()
    {
        $this->expectException(\LogicException::class);
        $this->expectExceptionMessage("Query builder can only have one build step");

        $builder = (new FilterQueryBuilder())
            ->onBuild(fn() => null);

        $builder->onBuild(fn() => null);
    }

    public function testBuildWithoutComposeStep()
    {
        $this->expectException(\LogicException::class);
        $this->expectExceptionMessage('Unusable query builder; no compose step');

        $builder = (new FilterQueryBuilder())
            ->onPrepare($this->createCallbackMock($this->once(), [], []))
        ;

        $builder->apply((object)[], []);
    }

    public function testBuildWithoutBuildStep()
    {
        $this->expectException(\LogicException::class);
        $this->expectExceptionMessage('Unusable query builder; no build step');

        $builder = (new FilterQueryBuilder())
            ->onPrepare($this->createCallbackMock($this->once(), [], []))
            ->onCompose($this->createCallbackMock($this->once(), [], []))
        ;

        $builder->apply((object)[], []);
    }

    /**
     * Similar to expectException, but also checks previous.
     */
    private function tryExpect(callable $fn, \Exception $expectedException)
    {
        try {
            $fn();
            $this->assertThat(null, new ExceptionConstraint(get_class($expectedException)));
        } catch (\Exception $exception) {
            $this->assertThat($exception, new ExceptionConstraint(get_class($expectedException)));
            $this->assertThat($exception, new ExceptionMessage($expectedException->getMessage()));

            $expectedPrevious = $expectedException->getPrevious();
            $previous = $exception->getPrevious();
            if ($expectedPrevious !== null) {
                $this->assertThat($previous, new ExceptionConstraint(get_class($expectedPrevious)));
                $this->assertThat($previous, new ExceptionMessage($expectedPrevious->getMessage()));
            }
        }
    }
}
