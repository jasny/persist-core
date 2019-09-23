<?php

declare(strict_types=1);

namespace Jasny\DB\Tests\Option;

use Jasny\DB\Option as opts;
use Jasny\DB\Option\ConflictResolutionOption;
use PHPUnit\Framework\TestCase;

/**
 * @covers \Jasny\DB\Option\ConflictResolutionOption
 */
class ConflictResolutionOptionTest extends TestCase
{
    public function resolutionProvider()
    {
        return array_map(fn($resolution) => [$resolution], ConflictResolutionOption::SUPPORTED);
    }

    /**
     * @dataProvider resolutionProvider
     */
    public function test($resolution)
    {
        $option = new ConflictResolutionOption($resolution);

        $this->assertEquals($resolution, $option->getResolution());
    }

    public function testWithUnsupportedResolution()
    {
        $this->expectException(\UnexpectedValueException::class);
        $this->expectExceptionMessage("Unsupported conflict resolution option 'foo-bar'");

        new ConflictResolutionOption('foo-bar');
    }

    public function testFunction()
    {
        $option = opts\existing('replace');

        $this->assertInstanceOf(ConflictResolutionOption::class, $option);
        $this->assertEquals('replace', $option->getResolution());
    }
}
