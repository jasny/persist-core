<?php

declare(strict_types=1);

namespace Jasny\Persist\Tests\Option;

use Jasny\Persist\Option\Functions as opt;
use Jasny\Persist\Option\SettingOption;
use Jasny\Persist\Option\OptionInterface;
use PHPUnit\Framework\TestCase;

/**
 * @covers \Jasny\Persist\Option\SettingOption
 */
class SettingOptionTest extends TestCase
{
    public function test()
    {
        $option = new SettingOption('foo.bar', 234);

        $this->assertEquals('foo.bar', $option->getName());
        $this->assertEquals(234, $option->getValue());
    }

    /**
     * @covers \Jasny\Persist\Option\Functions\setting
     */
    public function testFunction()
    {
        $option = opt\setting('foo.bar', 234);

        $this->assertInstanceOf(SettingOption::class, $option);
        $this->assertEquals('foo.bar', $option->getName());
        $this->assertEquals(234, $option->getValue());
    }

    public function testFind()
    {
        $opts = [
            $this->createMock(OptionInterface::class),
            new SettingOption('foo', 99),
            $this->createMock(OptionInterface::class),
            new SettingOption('foo.bar', 1), // Ignored
            $this->createMock(OptionInterface::class),
            new SettingOption('foo.bar', 234),
        ];

        $this->assertEquals(234, (new SettingOption('foo.bar', null))->findIn($opts));
        $this->assertEquals(42, (new SettingOption('zoo', 42))->findIn($opts));
    }

    public function testFindWithType()
    {
        $opts = [
            $this->createMock(OptionInterface::class),
            new SettingOption('foo', 'hello'),
            new SettingOption('foo', 'world'),
            new SettingOption('foo', (object)['a' => 'b']),
            new SettingOption('foo', 1),
        ];

        $string = (new SettingOption('foo', null))->findIn($opts, 'string');
        $this->assertEquals('world', $string);

        $object = (new SettingOption('foo', null))->findIn($opts, \stdClass::class);
        $this->assertEquals((object)['a' => 'b'], $object);
    }


    public function resolutionProvider()
    {
        return [
            'conflict' => ['conflict'],
            'ignore'   => ['ignore'],
            'replace'  => ['replace'],
            'update'   => ['update'],
        ];
    }

    /**
     * @covers \Jasny\Persist\Option\Functions\existing
     * @dataProvider resolutionProvider
     */
    public function testExistingFunction(string $resolution)
    {
        $option = opt\existing($resolution);

        $this->assertInstanceOf(SettingOption::class, $option);
        $this->assertEquals('existing', $option->getName());
        $this->assertEquals($resolution, $option->getValue());
    }

    /**
     * @covers \Jasny\Persist\Option\Functions\existing
     */
    public function testExistingFunctionWithUnsupportedResolution()
    {
        $this->expectException(\UnexpectedValueException::class);
        $this->expectExceptionMessage("Unsupported conflict resolution option 'foo-bar'");

        opt\existing('foo-bar');
    }
}
