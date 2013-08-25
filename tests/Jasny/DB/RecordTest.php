<?php
/**
 * Tests for Jasny\DB\Record.
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

/**
 * Tests for Record (without using a DB)
 * 
 * @package Test
 */
class RecordTest extends \PHPUnit_Framework_TestCase
{
    /**
     * Test Record::getValues
     */
    public function testGetValues()
    {
        $record = new \Bar();
        $this->assertEquals(array('id'=>null, 'description'=>'BAR', 'children'=>[]), $record->getValues());
    }
    
    /**
     * Test Record::setValues
     */
    public function testSetValues()
    {
        $record = new \Bar();
        $record->setValues(array('description'=>'CAFE', 'part'=>'hack'));
        
        $this->assertNull($record->id);
        $this->assertEquals('CAFE', $record->description);
        $this->assertEquals('u', $record->getPart());
    }
    
    /**
     * Test Record::getDBTable
     */
    public function testGetDBTable()
    {
        $record = new Record();
        $table = $this->getMockBuilder('Jasny\DB\Table')->disableOriginalConstructor()->getMockForAbstractClass();
        
        $record->_setDBTable($table);
        $this->assertSame($table, $record->getDBTable());
    }
    
    /**
     * Test Record::getDBTable
     */
    public function testSave()
    {
        $record = new Record();
        $record->id = 10;
        
        $table = $this->getMockBuilder('Jasny\DB\Table')->disableOriginalConstructor()->getMockForAbstractClass();
        $record->_setDBTable($table);
        
        $table->expects($this->once())->method('save')->with($this->equalTo($record));
        $record->save();
    }
    
}
