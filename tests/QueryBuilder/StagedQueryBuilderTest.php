<?php

declare(strict_types=1);

namespace Jasny\DB\Tests\QueryBuilder;

use Improved as i;
use Jasny\DB\Exception\BuildQueryException;
use Jasny\DB\Option\OptionInterface;
use Jasny\DB\QueryBuilder\StagedQueryBuilder;
use Jasny\TestHelper;
use PHPUnit\Framework\Constraint\Exception as ExceptionConstraint;
use PHPUnit\Framework\Constraint\ExceptionMessage;
use PHPUnit\Framework\MockObject\Builder\InvocationMocker;
use PHPUnit\Framework\TestCase;

/**
 * @covers \Jasny\DB\QueryBuilder\StagedQueryBuilder
 */
class StagedQueryBuilderTest extends TestCase
{
    use TestHelper;

    public function abProvider()
    {
        return [
            'buildQuery()' => ['method'],
            '__invoke()' => ['invoke'],
        ];
    }

    /**
     * @dataProvider abProvider
     * Testing all 'on*' methods at once. The importance is that they're executed in the correct order.
     */
    public function testBuild($ab)
    {
        $opts = [
            $this->createMock(OptionInterface::class),
            $this->createMock(OptionInterface::class)
        ];

        $composeCallbacks = [fn() => 1, fn() => 2];

        $builder = (new StagedQueryBuilder())
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
            ->onBuild($this->createCallbackMock( /* 4 */
                $this->once(),
                function (InvocationMocker $invoke) use ($composeCallbacks, $opts) {
                    $assertIterable = $this->callback(function ($arg) use ($composeCallbacks) {
                        return is_iterable($arg) && i\iterable_to_array($arg) === $composeCallbacks;
                    });

                    $invoke->with($assertIterable, $opts)->willReturn(['color: red', 'shape: square']);
                }
            ))
            ->onPrepare($this->createCallbackMock( /* 2 */
                $this->once(),
                [['foo' => 2, 'bar' => 10], $opts],
                ['foo' => 22, 'color' => 'red', 'shape' => 'square']
            ))
            ->onBuild($this->createCallbackMock( /* 6 */
                $this->once(),
                [['color: red', 'shape: square'], $opts],
                'color: red && shape: square'
            ))
        ;

        $result = ($ab === 'method')
            ? $builder->buildQuery(['foo' => 1, 'bar' => 10], $opts)
            : $builder(['foo' => 1, 'bar' => 10], $opts);

        $this->assertEquals('color: red && shape: square', $result);
    }

    public function testWithoutSteps()
    {
        $builder = (new StagedQueryBuilder())
            ->onPrepare($this->createCallbackMock($this->never()))
            ->onPrepare($this->createCallbackMock($this->never()))
            ->onCompose($this->createCallbackMock($this->never()))
            ->onCompose($this->createCallbackMock($this->never()))
            ->onBuild($this->createCallbackMock($this->never()))
            ->onBuild($this->createCallbackMock($this->never()))
            ->withoutPrepare()
            ->withoutCompose()
            ->onPrepare($this->createCallbackMock($this->once(), [], []))
            ->onCompose($this->createCallbackMock($this->once(), [], []))
            ->withoutBuild()
            ->onBuild($this->createCallbackMock($this->once(), [], []))
        ;

        $builder->buildQuery([]);
    }

    public function testWithoutSpecificSteps()
    {
        $never = $this->createCallbackMock($this->never());

        $builder = (new StagedQueryBuilder())
            ->onPrepare($never)
            ->onPrepare($this->createCallbackMock($this->once(), [], []))
            ->onCompose($never)
            ->onCompose($never)
            ->onBuild($this->createCallbackMock($this->once(), [], []))
            ->onBuild($never)
            ->withoutPrepare(fn($step) => ($step === $never))
            ->withoutCompose(fn($step) => ($step === $never))
            ->onPrepare($this->createCallbackMock($this->once(), [], []))
            ->onCompose($this->createCallbackMock($this->once(), [], []))
            ->withoutBuild(fn($step) => ($step === $never))
            ->onBuild($this->createCallbackMock($this->once(), [], []))
        ;

        $builder->buildQuery([]);
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

        $builder = (new StagedQueryBuilder())
            ->onPrepare($this->createCallbackMock($this->once(), [], []))
            ->onPrepare($callback)
            ->onPrepare($this->createCallbackMock($this->never()))
            ->onCompose($this->createCallbackMock($this->never()))
            ->onBuild($this->createCallbackMock($this->never()))
        ;

        $this->tryExpect(fn() => $builder->buildQuery([]), $buildException);
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

        $builder = (new StagedQueryBuilder())
            ->onPrepare($this->createCallbackMock($this->once(), [], []))
            ->onCompose($callback)
            ->onCompose($this->createCallbackMock($this->never()))
            ->onBuild($this->createCallbackMock($this->never()))
        ;

        $this->tryExpect(fn() => $builder->buildQuery([]), $buildException);
    }

    public function testBuildException()
    {
        $exception = new \RuntimeException("something's wrong");
        $callback = $this->createCallbackMock($this->once(), function (InvocationMocker $invoke) use ($exception) {
            $invoke->willThrowException($exception);
        });

        $buildException = new BuildQueryException(
            sprintf("Query builder failed in build step 1 of 2 (instance of %s)", get_class($callback)),
            0,
            $exception
        );

        $builder = (new StagedQueryBuilder())
            ->onPrepare($this->createCallbackMock($this->once(), [], []))
            ->onCompose($this->createCallbackMock($this->once(), [], []))
            ->onBuild($callback)
            ->onBuild($this->createCallbackMock($this->never()))
        ;

        $this->tryExpect(fn() => $builder->buildQuery([]), $buildException);
    }

    public function testImmutability()
    {
        $prepare = fn() => [];
        $compose = fn() => [];
        $build = fn() => '';

        $empty = new StagedQueryBuilder();
        $base = $empty
            ->onPrepare($prepare)
            ->onCompose($compose)
            ->onBuild($build)
        ;

        $this->assertNotSame($base, $base->onPrepare(fn() => []));
        $this->assertNotSame($base, $base->onCompose(fn() => []));
        $this->assertNotSame($base, $base->onBuild(fn() => []));

        $this->assertNotSame($base, $base->withoutPrepare());
        $this->assertNotSame($base, $base->withoutCompose());
        $this->assertNotSame($base, $base->withoutBuild());

        $this->assertNotSame($base, $base->withoutPrepare(fn() => true));
        $this->assertNotSame($base, $base->withoutCompose(fn() => true));
        $this->assertNotSame($base, $base->withoutBuild(fn() => true));

        $this->assertSame($base, $base->withoutPrepare(fn() => false));
        $this->assertSame($base, $base->withoutCompose(fn() => false));
        $this->assertSame($base, $base->withoutBuild(fn() => false));

        $this->assertSame($empty, $empty->withoutPrepare());
        $this->assertSame($empty, $empty->withoutCompose());
        $this->assertSame($empty, $empty->withoutBuild());
    }


    public function testPrepareCheckIterable()
    {
        $buildException = new BuildQueryException(
            sprintf("Query builder failed in prepare step 1 of 1"),
            0,
            new \UnexpectedValueException('Expected iterable, string(5) "hello" returned')
        );

        $builder = (new StagedQueryBuilder())
            ->onPrepare($this->createCallbackMock($this->once(), function (InvocationMocker $invoke) {
                $invoke->willReturn('hello');
            }))
            ->onCompose($this->createCallbackMock($this->never()))
            ->onBuild($this->createCallbackMock($this->never()))
        ;

        $this->tryExpect(fn() => $builder->buildQuery([]), $buildException);
    }

    public function testComposeCheckIterable()
    {
        $buildException = new BuildQueryException(
            sprintf("Query builder failed in compose step 1 of 1"),
            0,
            new \UnexpectedValueException('Expected iterable, string(5) "hello" returned')
        );

        $builder = (new StagedQueryBuilder())
            ->onPrepare($this->createCallbackMock($this->once(), [], []))
            ->onCompose($this->createCallbackMock($this->once(), function (InvocationMocker $invoke) {
                $invoke->willReturn('hello');
            }))
            ->onBuild($this->createCallbackMock($this->never()))
        ;

        $this->tryExpect(fn() => $builder->buildQuery([]), $buildException);
    }

    public function testComposeCheckCallables()
    {
        $callback = $this->createCallbackMock($this->once(), function (InvocationMocker $invoke) {
            $invoke->willReturn(['not_a_callable']);
        });

        $buildException = new BuildQueryException(
            sprintf("Query builder failed in build step 1 of 1"),
            0,
            new \UnexpectedValueException(sprintf('Not all items created in compose step 1 (instance of %s) are'
                . ' callable, got string(14) "not_a_callable"', get_class($callback)))
        );

        $builder = (new StagedQueryBuilder())
            ->onPrepare($this->createCallbackMock($this->once(), [], []))
            ->onCompose($callback)
            ->onBuild(fn(iterable $compose) => i\iterable_to_array($compose))
        ;

        $this->tryExpect(fn() => $builder->buildQuery([]), $buildException);
    }


    public function testBuildWithoutPrepareSteps()
    {
        $this->expectException(\LogicException::class);
        $this->expectExceptionMessage('Unusable query builder; no prepare step');

        $builder = new StagedQueryBuilder();

        $builder->buildQuery([]);
    }

    public function testBuildWithoutComposeSteps()
    {
        $this->expectException(\LogicException::class);
        $this->expectExceptionMessage('Unusable query builder; no compose step');

        $builder = (new StagedQueryBuilder())
            ->onPrepare($this->createCallbackMock($this->once(), [], []))
        ;

        $builder->buildQuery([]);
    }

    public function testBuildWithoutBuildSteps()
    {
        $this->expectException(\LogicException::class);
        $this->expectExceptionMessage('Unusable query builder; no build step');

        $builder = (new StagedQueryBuilder())
            ->onPrepare($this->createCallbackMock($this->once(), [], []))
            ->onCompose($this->createCallbackMock($this->once(), [], []))
        ;

        $builder->buildQuery([]);
    }

    /**
     * Similar to expectException, but also checks previous.
     */
    private function tryExpect(callable $fn, \Throwable $expectedException)
    {
        try {
            $fn();
            $this->assertThat(null, new ExceptionConstraint(get_class($expectedException)));
        } catch (\Throwable $exception) {
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
