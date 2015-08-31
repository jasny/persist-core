<?php

namespace Jasny;

use Prophecy\Prophet;

/**
 * Tests for Jasny\DB.
 * 
 * @package Test
 * @backupStaticAttributes enabled
 */
class DBTest extends \PHPUnit_Framework_TestCase
{
    /**
     * DB configuration
     * @var type 
     */
    protected static $config = [
        'red' => [
            'driver' => 'mysql',
            'host' => 'localhost',
            'username' => 'foo',
            'password' => 'bar',
            'database' => 'reddb',
            'charset' => 'utf8-general'
        ],
        'blue' => [
            'driver' => 'mongo',
            'host' => '192.168.1.20',
            'username' => 'foo',
            'password' => 'bar',
            'database' => 'bluedb'
        ],
        'default' => [
            'driver' => 'dummy',
            'foo' => 'bar',
            'year' => 2015
        ]
    ];

    /**
     * Class of dummy driver
     * @var string
     */
    protected static $dummyClass;
    
    
    /**
     * This method is called before the first test of this test class is run.
     */
    public static function setUpBeforeClass()
    {
        DB::$config = (object)static::$config;
        self::$dummyClass = self::addMockDriver('dummy');
    }
    
    /**
     * Add a mock driver
     * 
     * @param string $name
     * @return string
     */
    protected static function addMockDriver($name)
    {
        $prophet = new Prophet();
        $prophecy = $prophet->prophesize('Jasny\DB\Connection');
        
        $class = get_class($prophecy->reveal());
        DB::$drivers[$name] = $class;
        
        return $class;
    }
    
    /**
     * Test DB::getSettings()
     */
    public function testGetSettings()
    {
        $this->assertEquals(static::$config['red'], DB::getSettings('red'));
        $this->assertEquals(static::$config['blue'], DB::getSettings('blue'));
        $this->assertEquals(static::$config['default'], DB::getSettings('default'));
        
        $this->assertNull(DB::getSettings('nonexist'));
    }
    
    /**
     * Test DB::getConnectionClass()
     */
    public function testGetConnectionClass()
    {
        $fn = new \ReflectionMethod('Jasny\DB', 'getConnectionClass');
        $fn->setAccessible(true);
        
        $this->assertSame('Jasny\DB\MySQL\Connection', $fn->invoke(null, 'mysql'));
        $this->assertSame('Jasny\DB\REST\Client', $fn->invoke(null, 'rest'));
        $this->assertSame(self::$dummyClass, $fn->invoke(null, 'dummy'));
        
        $this->assertSame('Jasny\DB\MySQL\Connection', $fn->invoke(null, 'MySQL'));
    }
    
    /**
     * Test DB::getConnectionClass() with a single loaded driver
     */
    public function testGetConnectionClass_Default()
    {
        $fn = new \ReflectionMethod('Jasny\DB', 'getConnectionClass');
        $fn->setAccessible(true);
        
        DB::$drivers = ['dummy' => self::$dummyClass];
        $this->assertSame(self::$dummyClass, $fn->invoke(null));        
    }
    
    /**
     * Test DB::getConnectionClass() with a single loaded driver
     * 
     * @expectedException \Exception
     * @expectedExceptionMessage No Jasny DB drivers found
     */
    public function testGetConnectionClass_None()
    {
        $fn = new \ReflectionMethod('Jasny\DB', 'getConnectionClass');
        $fn->setAccessible(true);
        
        DB::$drivers = ['nonexistent' => 'Jasny\DB\NonExistent'];
        $fn->invoke(null);
    }
    
    /**
     * Test DB::getConnectionClass() with a single loaded driver
     * 
     * @expectedException \Exception
     * @expectedExceptionMessage Please specify the database driver. The following are supported: dummy, foo
     */
    public function testGetConnectionClass_Err()
    {
        $fn = new \ReflectionMethod('Jasny\DB', 'getConnectionClass');
        $fn->setAccessible(true);
        
        DB::$drivers = [
            'nonexistent' => 'Jasny\DB\NonExistent',
            'dummy' => self::$dummyClass
        ];
        self::addMockDriver('foo');
        
        $fn->invoke(null);
    }
}
