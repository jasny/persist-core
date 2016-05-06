<?php

namespace Jasny;

use Jasny\DB\Connection;
use Jasny\DB\ConnectionRegistry;

/**
 * @covers Jasny\DB
 */
class DBTest extends \PHPUnit_Framework_TestCase
{
    /**
     * Run before each test
     */
    protected function setUp()
    {
        DB::resetGlobalState();
    }
    
    public function testConfigure()
    {
        $config = [
            'red' => 'http://www.example.com',
            'blue' => (object)[
                'driver' => 'dummy',
                'type' => 'color'
            ],
            'default' => [
                'driver' => 'dummy',
                'foo' => 'bar',
                'year' => 2015
            ]
        ];
        
        DB::configure($config);
        
        $this->assertEquals($config['red'], DB::getSettings('red'));
        $this->assertEquals($config['blue'], DB::getSettings('blue'));
        $this->assertEquals($config['default'], DB::getSettings('default'));
        
        $this->assertNull(DB::getSettings('nonexist'));
    }
    
    /**
     * Test DB::conn()
     */
    public function testConn()
    {
        $mockConnection = $this->getMockForAbstractClass(Connection::class);
        
        $mockConnectionRegistry = $this->getMock(ConnectionRegistry::class);
        $mockConnectionRegistry->expects($this->once())->method('get')->with('default')->willReturn($mockConnection);
        
        DB::employ($mockConnectionRegistry);
        
        $connection = DB::conn();
        $this->assertSame($mockConnection, $connection);
    }
}
