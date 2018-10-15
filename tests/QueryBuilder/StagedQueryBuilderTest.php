<?php

namespace Jasny\DB\Tests\QueryBuilder;

use Improved as i;
use Jasny\DB\QueryBuilder\FilterParser;
use Jasny\DB\QueryBuilder\StagedQueryBuilder;
use Jasny\TestHelper;
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
            ['A'],
            ['B']
        ];
    }

    /**
     * @dataProvider abProvider
     * Testing all 'on*' methods at once. The importance is that they're executed in the correct order.
     */
    public function test($ab)
    {
        $builder = (new StagedQueryBuilder())
            ->onPrepare($this->createCallbackMock(
                $this->once(),
                [['foo' => 1, 'bar' => 10], ['limit' => 5]],
                ['foo' => 2, 'bar' => 10]
            ))
            ->onCompose($this->createCallbackMock(
                $this->once(),
                [['foo' => 22, 'bar' => 100], ['limit' => 5]],
                ['color' => 'red', 'shape' => 'square']
            ))
            ->onBuild($this->createCallbackMock(
                $this->once(),
                [['color' => 'red', 'shape' => 'square'], ['limit' => 5]],
                ['color: red', 'shape: square']
            ))
            ->onFinalize($this->createCallbackMock(
                $this->once(),
                [['color: red', 'shape: square', 'abc: 123'], ['limit' => 5]],
                'color: red && shape: square && abc: 123'
            ))
            ->onPrepare($this->createCallbackMock(
                $this->once(),
                [['foo' => 2, 'bar' => 10], ['limit' => 5]],
                ['foo' => 22, 'bar' => 100]
            ))
            ->onBuild($this->createCallbackMock(
                $this->once(),
                [['color: red', 'shape: square'], ['limit' => 5]],
                ['color: red', 'shape: square', 'abc: 123']
            ));

        $result = ($ab === 'A')
            ? $builder->buildQuery(['foo' => 1, 'bar' => 10], ['limit' => 5])
            : $builder(['foo' => 1, 'bar' => 10], ['limit' => 5]);

        $this->assertEquals('color: red && shape: square && abc: 123', $result);
    }

    public function filterProvider()
    {
        return [
            ['foo', ''],
            ['foo (min)', 'min']
        ];
    }

    /**
     * @dataProvider filterProvider
     */
    public function testWithFilter(string $key, string $operator)
    {
        $base = (new StagedQueryBuilder())
            ->onPrepare(new FilterParser())
            ->onCompose(function(iterable $iterable) {
                foreach ($iterable as $info => $value) {
                    $info['value'] = $value;
                    $fn = function($value) {
                        return $value;
                    };

                    yield $info => $fn;
                }
            })
            ->onBuild(function(iterable $iterable) {
                foreach ($iterable as $info => $callable) {
                    yield 'bar' => $callable($info['field'], $info['operator'], $info['value']);
                }
            })
            ->onFinalize(function(iterable $iterable) {
                return i\iterable_to_array($iterable, true);
            });

        $builder = $base->withFilter('foo', $this->createCallbackMock(
            $this->once(),
            ['foo', $operator, 42],
            99
        ));
        $this->assertNotSame($base, $builder);
        $this->assertInstanceOf(StagedQueryBuilder::class, $builder);

        $result = $builder->buildQuery([$key => 42]);

        $this->assertEquals(['bar' => 99], $result);
    }
}
