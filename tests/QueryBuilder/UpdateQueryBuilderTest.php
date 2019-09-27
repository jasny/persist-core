<?php

declare(strict_types=1);

namespace Jasny\DB\Tests\QueryBuilder;

use Jasny\DB\Update\UpdateInstruction;
use Jasny\DB\Option\OptionInterface;
use Jasny\DB\QueryBuilder\UpdateQueryBuilder;
use Jasny\TestHelper;
use PHPUnit\Framework\Constraint\Exception as ExceptionConstraint;
use PHPUnit\Framework\Constraint\ExceptionMessage;
use PHPUnit\Framework\MockObject\Builder\InvocationMocker;
use PHPUnit\Framework\TestCase;

/**
 * @covers \Jasny\DB\QueryBuilder\UpdateQueryBuilder
 */
class UpdateQueryBuilderTest extends TestCase
{
    use TestHelper;

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
        $update = [
            new UpdateInstruction('set', ['foo' => 42, 'color' => 'blue']),
            new UpdateInstruction('inc', ['bar' => 1]),
            new UpdateInstruction('set', ['fruit' => 'pear']),
        ];

        $invocation = function (InvocationMocker $invoke) use ($update) {
            $invoke->withConsecutive(
                [$this->identicalTo($this->acc), $this->identicalTo($update[0]), $this->identicalTo($this->opts)],
                [$this->identicalTo($this->acc), $this->identicalTo($update[1]), $this->identicalTo($this->opts)],
                [$this->identicalTo($this->acc), $this->identicalTo($update[2]), $this->identicalTo($this->opts)]
            );
        };
        $defaultLogic = $this->createCallbackMock($this->exactly(3), $invocation);

        $builder = new UpdateQueryBuilder($defaultLogic);
        $builder->apply($this->acc, $update, $this->opts);
    }

    public function testPreparation()
    {
        $prepared = [
            new UpdateInstruction('set', ['FOO' => 42, 'color' => 'blue', 'fruit' => 'pear']),
            new UpdateInstruction('inc', ['BAR' => 1]),
        ];

        $invocation = function (InvocationMocker $invoke) use ($prepared) {
            $invoke->withConsecutive(
                [$this->identicalTo($this->acc), $prepared[0], $this->identicalTo($this->opts)],
                [$this->identicalTo($this->acc), $prepared[1], $this->identicalTo($this->opts)],
            );
        };
        $defaultLogic = $this->createCallbackMock($this->exactly(2), $invocation);

        $update = [
            new UpdateInstruction('set', ['foo' => 42, 'color' => 'blue']),
            new UpdateInstruction('inc', ['bar' => 1]),
            new UpdateInstruction('set', ['fruit' => 'pear']),
        ];

        $prepare = $this->createCallbackMock(
            $this->once(),
            [$this->identicalTo($update), $this->identicalTo($this->opts)],
            $prepared
        );

        $builder = (new UpdateQueryBuilder($defaultLogic))->withPreparation($prepare);
        $builder->apply($this->acc, $update, $this->opts);
    }

    public function testFinalization()
    {
        $defaultLogic = $this->createCallbackMock($this->exactly(3), []);

        $finalize = $this->createCallbackMock(
            $this->once(),
            [$this->identicalTo($this->acc), $this->identicalTo($this->opts)]
        );

        $update = [
            new UpdateInstruction('set', ['foo' => 42, 'color' => 'blue']),
            new UpdateInstruction('inc', ['bar' => 1]),
            new UpdateInstruction('set', ['fruit' => 'pear']),
        ];

        $builder = (new UpdateQueryBuilder($defaultLogic))->withFinalization($finalize);
        $builder->apply($this->acc, $update, $this->opts);
    }

    public function testWithCustomUpdateOperator()
    {
        $update = [
            new UpdateInstruction('set', ['foo' => 42, 'color' => 'blue']),
            new UpdateInstruction('pike', ['bar' => 1]),
            new UpdateInstruction('swap', ['fruit' => 'pear']),
        ];

        $defaultLogic = $this->createCallbackMock(
            $this->once(),
            [$this->identicalTo($this->acc), $this->identicalTo($update[0]), $this->identicalTo($this->opts)]
        );

        $customPike = $this->createCallbackMock(
            $this->once(),
            [$this->identicalTo($this->acc), $this->identicalTo($update[1]), $this->identicalTo($this->opts)]
        );

        $customSwap = $this->createCallbackMock(
            $this->once(),
            [$this->identicalTo($this->acc), $this->identicalTo($update[2]), $this->identicalTo($this->opts)]
        );

        $builder = (new UpdateQueryBuilder($defaultLogic))
            ->withCustomUpdateOperator('pike', $customPike)
            ->withCustomUpdateOperator('swap', $customSwap);

        $builder->apply($this->acc, $update, $this->opts);
    }

    public function testWithoutCustomUpdateOperator()
    {
        $update = [
            new UpdateInstruction('set', ['foo' => 42, 'color' => 'blue']),
            new UpdateInstruction('pike', ['bar' => 1]),
            new UpdateInstruction('swap', ['fruit' => 'pear']),
        ];

        $invocation = function (InvocationMocker $invoke) use ($update) {
            $invoke->withConsecutive(
                [$this->identicalTo($this->acc), $update[0], $this->identicalTo($this->opts)],
                [$this->identicalTo($this->acc), $update[1], $this->identicalTo($this->opts)],
            );
        };
        $defaultLogic = $this->createCallbackMock($this->exactly(2), $invocation);

        $customPike = $this->createCallbackMock($this->never());

        $customSwap = $this->createCallbackMock(
            $this->once(),
            [$this->identicalTo($this->acc), $this->identicalTo($update[2]), $this->identicalTo($this->opts)]
        );

        $builder = (new UpdateQueryBuilder($defaultLogic))
            ->withCustomUpdateOperator('pike', $customPike)
            ->withCustomUpdateOperator('swap', $customSwap)
            ->withoutCustomUpdateOperator('pike');

        $builder->apply($this->acc, $update, $this->opts);
    }

    /**
     * {@internal Calling next is difficult with mocked callbacks, so not mocking.}}
     */
    public function testWithCustomUpdateCombo()
    {
        $defaultLogic = function ($acc, $item, $opts) {
            $this->assertSame($this->acc, $acc);
            $this->assertEquals(new UpdateInstruction('inc', ['foo' => 0.42]), $item);
            $this->assertSame($this->opts, $opts);

            $acc->default = true;
        };

        $customPike = function ($acc, $item, $opts, $next) {
            $this->assertSame($this->acc, $acc);
            $this->assertEquals(new UpdateInstruction('pike', ['foo' => 42]), $item);
            $this->assertSame($this->opts, $opts);
            $this->assertIsCallable($next);

            $acc->pike = true;
            $next(new UpdateInstruction('inc', ['foo' => 0.42]));
        };

        $builder = (new UpdateQueryBuilder($defaultLogic))
            ->withCustomUpdateOperator('pike', $customPike);

        $builder->apply($this->acc, [new UpdateInstruction('pike', ['foo' => 42])], $this->opts);

        $this->assertEquals(['pike' => true, 'default' => true], (array)$this->acc);
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
