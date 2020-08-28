<?php

declare(strict_types=1);

namespace Jasny\Persist\Tests\Filter;

use Jasny\Persist\Filter\FilterItem;
use PHPUnit\Framework\TestCase;

/**
 * @covers \Jasny\Persist\Filter\FilterItem
 */
class FilterItemTest extends TestCase
{
    public function test()
    {
        $item = new FilterItem('foo', 'inc', 12);

        $this->assertEquals('foo', $item->getField());
        $this->assertEquals('inc', $item->getOperator());
        $this->assertEquals(12, $item->getValue());
    }
}
