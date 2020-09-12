<?php

declare(strict_types=1);

namespace Persist\Tests\Filter;

use Persist\Filter\FilterItem;
use PHPUnit\Framework\TestCase;

/**
 * @covers \Persist\Filter\FilterItem
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
