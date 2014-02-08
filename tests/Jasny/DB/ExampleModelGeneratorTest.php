<?php
/**
 * Tests for Jasny\DB\ModelGenerator.
 * 
 * The MySQL user needs to have full permissions for `dbtest`.*.
 * 
 * Please configure default mysqli settings in your php.ini.
 * Alternatively run as `php -d mysqli.default_user=USER -d mysqli.default_pw=PASSWORD /usr/bin/phpunit`
 * 
 * @author Arnold Daniels
 */
/** */

namespace Jasny\DB;

use org\bovigo\vfs\vfsStream, org\bovigo\vfs\visitor\vfsStreamPrintVisitor;

/**
 * Tests for ModelGenerator (without using a DB)
 * 
 * @package Test
 * @backupStaticAttributes enabled
 */
class ExampleModelGeneratorTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \vfsStreamDirectory
     */
    private $root;

    /**
     * Call a protected method
     * 
     * @param string $class
     * @param string $name   Method name
     * @param array  $args
     * @return mixed
     */
    protected static function call($class, $name, $args)
    {
        $method = new \ReflectionMethod($class, $name);
        $method->setAccessible(true);
        return $method->invokeArgs(null, $args);
    }

    /**
     * Get a protected property
     * 
     * @param string $class
     * @param string $name   Property name
     * @return mixed
     */
    protected static function get($class, $name)
    {
        $property = new \ReflectionProperty($class, $name);
        $property->setAccessible(true);
        return $property->getValue(null);
    }
    
    /**
     * Set a protected property
     * 
     * @param string $class
     * @param string $name   Property name
     * @param mixed  $value
     */
    protected static function set($class, $name, $value)
    {
        $property = new \ReflectionProperty($class, $name);
        $property->setAccessible(true);
        $property->setValue(null, $value);
    }
    
    /**
     * set up test environmemt
     */
    public function setUp()
    {
        $this->root = vfsStream::setup('cache');
    }
    
    /**
     * set up test environmemt
     */
    public function tearDown()
    {
        foreach (spl_autoload_functions() as $fn) {
            if (is_array($fn) && is_string($fn[0]) && strpos($fn[0], 'ModelGenerator') !== false) {
                spl_autoload_unregister($fn);
            }
        }
    }
    
    /**
     * Test enable
     */
    public function testEnable()
    {
        ModelGenerator::enable(vfsStream::url('cache'));
        
        $this->assertSame(vfsStream::url('cache'), self::get('Jasny\DB\ModelGenerator', 'cachePath'));
        $this->assertContains(['Jasny\DB\ModelGenerator', 'autoload'], spl_autoload_functions());
    }
    
    /**
     * Test cache and load
     */
    public function testCacheAndLoad()
    {
        $class = $this->getMockClass('Jasny\DB\ModelGenerator', ['load']);
        self::set($class, 'cachePath', vfsStream::url('cache'));
        
        $class::staticExpects($this->once())
             ->method('load')
             ->with($this->equalTo('vfs://cache/test.php'))
             ->will($this->returnValue(true));
        
        self::call($class, 'cacheAndLoad', ['test', '<?php // test']);
        
        //vfsStream::inspect(new vfsStreamPrintVisitor(), $this->root); // Output the virtual file structure
        
        $this->assertTrue($this->root->hasChild('test.php'));
        $this->assertEquals(file_get_contents(vfsStream::url('cache') . "/test.php"), '<?php // test');
    }
}
