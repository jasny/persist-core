<?php
/**
 * Tests for Jasny\MySQL\DB.
 * 
 * The MySQL user needs to have full permissions for `dbtest`.*.
 * 
 * Please configure default mysqli settings in your php.ini.
 * Alternatively run as `php -d mysqli.default_user=USER -d mysqli.default_pw=PASSWORD /usr/bin/phpunit`
 * 
 * @author Arnold Daniels
 */
/** */

namespace Jasny\DB\MySQL;

require_once __DIR__ . '/TestCase.php';

/**
 * Tests for MySQL\Connection.
 * 
 * @package Test
 * @subpackage MySQL
 */
class ConnectionTest extends TestCase
{
    /**
     * Test Connection::conn()
     */
    public function testConn()
    {
        $conn = Connection::conn();
        $this->assertInstanceOf('Jasny\DB\MySQL\Connection', $conn);
        $this->assertSame($this->db, $conn);

        list($dbname) = mysqli_query($conn, "SELECT DATABASE()")->fetch_row();
        $this->assertEquals('dbtest', $dbname);

        $this->assertSame($conn, Connection::conn());
    }
    
    /**
     * Test Connection::__construct() and Connection::close()
     */
    public function testConstruct()
    {
        $this->disconnectDB();
        
        $settings = (object)array('host'=>ini_get('mysqli.default_host'), 'username'=>ini_get('mysqli.default_user') ?: 'root', 'password'=>ini_get('mysqli.default_pw'), 'dbname'=>'dbtest');
        $this->db = new Connection($settings);
        
        list($dbname) = mysqli_query($this->db, "SELECT DATABASE()")->fetch_row();
        $this->assertEquals('dbtest', $dbname);
        
        $this->assertSame($this->db, Connection::conn());
    }    

    /**
     * Test Connection::asDefault()
     */
    public function testAsDefault()
    {
        $olddb = $this->db;
        $this->db = new Connection(ini_get('mysqli.default_host'), ini_get('mysqli.default_user') ?: 'root', ini_get('mysqli.default_pw'), 'dbtest');
        $olddb->close();

        $this->db->asDefault();
        $this->assertSame($this->db, Connection::conn());
        $this->assertSame($this->db, \Jasny\DB\Table::$defaultConnection);
    }
    
            
    /**
     * Test Connection::quote()
     */
    public function testQuote()
    {
        $this->assertSame('TRUE', Connection::quote(true));
        $this->assertSame('FALSE', Connection::quote(false));

        $this->assertSame('NULL', Connection::quote(null));
        $this->assertSame('DEFAULT', Connection::quote(null, 'DEFAULT'));

        $this->assertSame('10', Connection::quote(10));
        $this->assertSame('-20', Connection::quote(-20));
        $this->assertSame('3.1415', Connection::quote(3.1415));

        $this->assertSame('"jan"', Connection::quote('jan'));
        $this->assertSame('"Quoting \"It\'s Fantastic\""', Connection::quote('Quoting "It\'s Fantastic"'));
        $this->assertSame('"Multi\nline\ntext"', Connection::quote("Multi\nline\ntext"));
        $this->assertSame('"Yet\\\\Another\\\\Namespace"', Connection::quote('Yet\Another\Namespace')); // 2 escaped backslases = 4 backslashes

        $this->assertSame('(10, 20, 99)', Connection::quote(array(10, 20, 99)));
        $this->assertSame('(10, NULL, "jan", TRUE)', Connection::quote(array(10, null, 'jan', true)));
        
        $this->assertSame('"1981-08-22 00:00:00"', Connection::quote(new \DateTime('22-08-1981')));
        $this->assertSame('"2013-03-01 16:04:10"', Connection::quote(new \DateTime('01-03-2013 16:04:10')));
        
        if (date_default_timezone_get()) {
            $datetime = new \DateTime('2013-03-01 16:04:10+00:00');
            $datetime->setTimezone(new \DateTimeZone(date_default_timezone_get()));
            $this->assertSame('"' . $datetime->format('Y-m-d H:i:s') . '"', Connection::quote(new \DateTime('2013-03-01 16:04:10+00:00')));
        }
    }

    /** 
     * Test Connection::backquote()
     */
    public function testBackquote()
    {
        $this->assertSame('`name`', Connection::backquote('name'));
        $this->assertSame('`name with spaces`', Connection::backquote('name with spaces'));
        $this->assertSame('`name, password`', Connection::backquote('name`, `password'));
    }

    /**
     * Test Connection::bind() with basic placeholders
     * 
     * @depends testQuote
     */
    public function testBind()
    {
        $query = Connection::bind('SELECT * FROM foo');
        $this->assertEquals('SELECT * FROM foo', $query);

        $query = Connection::bind('SELECT * FROM foo WHERE id = ?', 10);
        $this->assertEquals('SELECT * FROM foo WHERE id = 10', $query);

        $query = Connection::bind('SELECT * FROM foo WHERE name = ?', 'jan');
        $this->assertEquals('SELECT * FROM foo WHERE name = "jan"', $query);

        $query = Connection::bind('SELECT * FROM foo WHERE active = ?', true);
        $this->assertEquals('SELECT * FROM foo WHERE active = TRUE', $query);

        $query = Connection::bind('SELECT *, "a\"b?d" AS `str?` FROM foo WHERE id = ?', 10);
        $this->assertEquals('SELECT *, "a\"b?d" AS `str?` FROM foo WHERE id = 10', $query);

        $query = Connection::bind('SELECT * FROM foo WHERE id = ? AND active = ? LIMIT ?', 10, true, 15);
        $this->assertEquals('SELECT * FROM foo WHERE id = 10 AND active = TRUE LIMIT 15', $query);

        $query = Connection::bind('UPDATE foo SET data = ? WHERE id = ?', null, 10);
        $this->assertEquals('UPDATE foo SET data = NULL WHERE id = 10', $query);

        $query = Connection::bind('SELECT * FROM foo WHERE id = ? AND active = ?', 10);
        $this->assertEquals('SELECT * FROM foo WHERE id = 10 AND active = ?', $query);

        $query = Connection::bind('SELECT * FROM foo WHERE id IN ?', array(10, 20, 99));
        $this->assertEquals('SELECT * FROM foo WHERE id IN (10, 20, 99)', $query);
    }

    /**
     * Test Connection::bind() with named placeholders
     * 
     * @depends testQuote
     */
    public function testBind_Named()
    {
        $query = Connection::bind('SELECT * FROM foo WHERE id = :id', array('id' => 10));
        $this->assertEquals('SELECT * FROM foo WHERE id = 10', $query);

        $query = Connection::bind('SELECT * FROM foo WHERE name = :name', array('name' => 'jan'));
        $this->assertEquals('SELECT * FROM foo WHERE name = "jan"', $query);

        $query = Connection::bind('SELECT *, "a\"b:id" AS `str:id` FROM foo WHERE id = :id', array('id' => 10));
        $this->assertEquals('SELECT *, "a\"b:id" AS `str:id` FROM foo WHERE id = 10', $query);

        $query = Connection::bind('SELECT * FROM foo WHERE id = :id AND active = :active LIMIT :limit', array('id' => 10, 'active' => true, 'limit' => 15));
        $this->assertEquals('SELECT * FROM foo WHERE id = 10 AND active = TRUE LIMIT 15', $query);

        $query = Connection::bind('UPDATE foo SET data = :data WHERE id = :id', array('data' => null, 'id' => 10));
        $this->assertEquals('UPDATE foo SET data = NULL WHERE id = 10', $query);

        $query = Connection::bind('SELECT * FROM foo WHERE id = :id AND active = :active', array('id' => 10));
        $this->assertEquals('SELECT * FROM foo WHERE id = 10 AND active = :active', $query);

        $query = Connection::bind('SELECT * FROM foo WHERE id IN :ids', array('ids' => array(10, 20, 99)));
        $this->assertEquals('SELECT * FROM foo WHERE id IN (10, 20, 99)', $query);
    }

    /**
     * Test Connection::query() with basic placeholders
     * 
     * @depends testConn
     * @depends testBind
     */
    public function testQuery()
    {
        $result = Connection::conn()->query("SELECT id, name FROM foo ORDER BY id");
        $this->assertInstanceOf('mysqli_result', $result);
        $this->assertEquals(5, $result->num_rows);
        $this->assertEquals(array('id' => 1, 'name' => 'Foo'), $result->fetch_assoc());
        // No need to check all rows

        $result = Connection::conn()->query("SELECT id FROM foo WHERE ext = 'n/a'");
        $this->assertInstanceOf('mysqli_result', $result);
        $this->assertEquals(0, $result->num_rows);

        $result = Connection::conn()->query("SELECT id, name FROM foo WHERE id = ?", 4);
        $this->assertInstanceOf('mysqli_result', $result);
        $this->assertEquals(1, $result->num_rows);
        $this->assertEquals(array('id' => 4, 'name' => 'Man'), $result->fetch_assoc());

        $result = Connection::conn()->query("SELECT id, name FROM foo WHERE id > ? AND ext = ?", 1, 'tv');
        $this->assertInstanceOf('mysqli_result', $result);
        $this->assertEquals(1, $result->num_rows);
        $this->assertEquals(array('id' => 3, 'name' => 'Zoo'), $result->fetch_assoc());

        $result = Connection::conn()->query("SELECT id, name FROM foo WHERE id IN ?", array(1, 4));
        $this->assertInstanceOf('mysqli_result', $result);
        $this->assertEquals(2, $result->num_rows);
        $this->assertEquals(array('id' => 1, 'name' => 'Foo'), $result->fetch_assoc());
        $this->assertEquals(array('id' => 4, 'name' => 'Man'), $result->fetch_assoc());

        $result = Connection::conn()->query('SELECT id, name FROM foo WHERE id = :id', array('id' => 4));
        $this->assertEquals(1, $result->num_rows);
        $this->assertEquals(array('id' => 4, 'name' => 'Man'), $result->fetch_assoc());
    }

    /**
     * Test Connection::query() with named placeholders
     * 
     * @depends testConn
     * @depends testBind_Named
     */
    public function testQuery_Named()
    {
        $result = Connection::conn()->query("SELECT id, name FROM foo WHERE id > :id AND ext = :ext", array('id' => 1, 'ext' => 'tv'));
        $this->assertInstanceOf('mysqli_result', $result);
        $this->assertEquals(1, $result->num_rows);
        $this->assertEquals(array('id' => 3, 'name' => 'Zoo'), $result->fetch_assoc());
    }

    /**
     * Test Connection::query() with an update query
     * 
     * @depends testConn
     */
    public function testQuery_Write()
    {
        self::$reuse_db = false;

        $result = Connection::conn()->query("UPDATE foo SET name='TEST' WHERE ext='tv'");
        $this->assertTrue($result);
        $this->assertEquals(2, Connection::conn()->affected_rows);

        $result = Connection::conn()->query("SELECT id, name FROM foo ORDER BY id");
        $this->assertInstanceOf('mysqli_result', $result);
        $this->assertEquals(5, $result->num_rows);
        $this->assertEquals(array('id' => 1, 'name' => 'TEST'), $result->fetch_assoc());
        $this->assertEquals(array('id' => 2, 'name' => 'Bar'), $result->fetch_assoc());
    }

    /**
     * Test MySQL\Exception by trying to execute and invalid query
     * 
     * @depends testQuery
     */
    public function testQuery_Exception()
    {
        $query = "SELECT * FROM foobar";

        try {
            Connection::conn()->query($query);
            $this->fail("No MySQL\Exception was thrown");
        } catch (Exception $e) {
            $this->assertInstanceOf('Jasny\DB\MySQL\Exception', $e);
            $this->assertEquals($query, $e->getQuery());
            $this->assertEquals(1146, $e->getCode());
            $this->assertEquals("Table 'dbtest.foobar' doesn't exist", $e->getError());
            $this->assertEquals("Query has failed. Table 'dbtest.foobar' doesn't exist.\n$query", $e->getMessage());
        }
    }

    /**
     * Test Connection::fetchAll()
     * 
     * @depends testQuery
     */
    public function testFetchAll()
    {
        $rows = Connection::conn()->fetchAll("SELECT id, name FROM foo ORDER BY id");
        $this->assertEquals(array(array('id' => 1, 'name' => 'Foo'), array('id' => 2, 'name' => 'Bar'), array('id' => 3, 'name' => 'Zoo'), array('id' => 4, 'name' => 'Man'), array('id' => 5, 'name' => 'Ops')), $rows);

        $rows = Connection::conn()->fetchAll("SELECT id, name FROM foo ORDER BY id", MYSQLI_NUM);
        $this->assertEquals(array(array(1, 'Foo'), array(2, 'Bar'), array(3, 'Zoo'), array(4, 'Man'), array(5, 'Ops')), $rows);

        $rows = Connection::conn()->fetchAll("SELECT id, name FROM foo ORDER BY id", MYSQLI_BOTH);
        $this->assertEquals(array(array(1, 'Foo', 'id' => 1, 'name' => 'Foo'), array(2, 'Bar', 'id' => 2, 'name' => 'Bar'), array(3, 'Zoo', 'id' => 3, 'name' => 'Zoo'), array(4, 'Man', 'id' => 4, 'name' => 'Man'), array(5, 'Ops', 'id' => 5, 'name' => 'Ops')), $rows);

        $rows = Connection::conn()->fetchAll("SELECT id FROM foo WHERE ext = 'n/a'");
        $this->assertEquals(array(), $rows);

        $rows = Connection::conn()->fetchAll("SELECT id, name FROM foo WHERE ext = ?", MYSQLI_ASSOC, 'tv');
        $this->assertEquals(array(array('id' => 1, 'name' => 'Foo'), array('id' => 3, 'name' => 'Zoo')), $rows);

        $rows = Connection::conn()->fetchAll("SELECT id, name FROM foo WHERE id > ? AND ext IN ?", MYSQLI_ASSOC, 1, array('tv', 'rs'));
        $this->assertEquals(array(array('id' => 3, 'name' => 'Zoo'), array('id' => 5, 'name' => 'Ops')), $rows);

        $rows = Connection::conn()->fetchAll("SELECT id, name FROM foo WHERE id IN ?", MYSQLI_ASSOC, array(1, 4));
        $this->assertEquals(array(array('id' => 1, 'name' => 'Foo'), array('id' => 4, 'name' => 'Man')), $rows);

        $rows = Connection::conn()->fetchAll("SELECT id, name FROM foo WHERE ext = :ext", MYSQLI_ASSOC, array('ext' => 'tv'));
        $this->assertEquals(array(array('id' => 1, 'name' => 'Foo'), array('id' => 3, 'name' => 'Zoo')), $rows);
    }

    /**
     * Test Connection::fetchAll() fetching objects
     * 
     * @depends fetchAll
     */
    public function testFetchAll_object()
    {
        $class = get_class($this->getMock('stdClass'));
        
        $obj1 = new $class();
        $obj1->id = 1;
        $obj1->name = 'Foo';
        
        $obj2 = new $class();
        $obj2->id = 3;
        $obj2->name = 'Zoo';
        
        $rows = Connection::conn()->fetchAll("SELECT id, name FROM foo WHERE ext = ?", $class, 'tv');
        $this->assertEquals(array($obj1, $obj2), $rows);
    }
    
    /**
     * Test Connection::fetchAll() with a result
     * 
     * @depends testQuery
     */
    public function testFetchAll_result()
    {
        $result = Connection::conn()->query("SELECT id, name FROM foo ORDER BY id");
        $rows = Connection::conn()->fetchAll($result);
        $this->assertEquals(array(array('id' => 1, 'name' => 'Foo'), array('id' => 2, 'name' => 'Bar'), array('id' => 3, 'name' => 'Zoo'), array('id' => 4, 'name' => 'Man'), array('id' => 5, 'name' => 'Ops')), $rows);
    }

    /**
     * Test Connection::fetchOne()
     * 
     * @depends testQuery
     */
    public function testFetchOne()
    {
        $row = Connection::conn()->fetchOne("SELECT id, name FROM foo WHERE id = 4");
        $this->assertEquals(array('id' => 4, 'name' => 'Man'), $row);

        $row = Connection::conn()->fetchOne("SELECT id, name FROM foo ORDER BY id");
        $this->assertEquals(array('id' => 1, 'name' => 'Foo'), $row);

        $row = Connection::conn()->fetchOne("SELECT id, name FROM foo WHERE id = 4", MYSQLI_NUM);
        $this->assertEquals(array(4, 'Man'), $row);

        $row = Connection::conn()->fetchOne("SELECT id, name FROM foo WHERE id = 4", MYSQLI_BOTH);
        $this->assertEquals(array(4, 'Man', 'id' => 4, 'name' => 'Man'), $row);

        $row = Connection::conn()->fetchOne("SELECT id FROM foo WHERE ext = 'n/a'");
        $this->assertNull($row);

        $row = Connection::conn()->fetchOne("SELECT id, name FROM foo WHERE id = ?", MYSQLI_ASSOC, 4);
        $this->assertEquals(array('id' => 4, 'name' => 'Man'), $row);

        $row = Connection::conn()->fetchOne("SELECT id, name FROM foo WHERE id > ? AND ext IN ?", MYSQLI_ASSOC, 1, array('tv', 'n/a'));
        $this->assertEquals(array('id' => 3, 'name' => 'Zoo'), $row);

        $row = Connection::conn()->fetchOne("SELECT id, name FROM foo WHERE id = :id", MYSQLI_ASSOC, array('id' => 4));
        $this->assertEquals(array('id' => 4, 'name' => 'Man'), $row);
    }

    /**
     * Test Connection::fetchOne() with an object
     * 
     * @depends testFetchOne
     */
    public function testFetchOne_object()
    {
        $class = get_class($this->getMock('stdClass'));
        
        $obj = new $class();
        $obj->id = 4;
        $obj->name = 'Man';
        
        $row = Connection::conn()->fetchOne("SELECT id, name FROM foo WHERE id = 4", $class);
        $this->assertEquals($obj, $row);
    }

    /**
     * Test Connection::fetchOne() with a result
     * 
     * @depends testQuery
     */
    public function testFetchOne_result()
    {
        $result = Connection::conn()->query("SELECT id, name FROM foo WHERE id = 4");
        $row = Connection::conn()->fetchOne($result);
        $this->assertEquals(array('id' => 4, 'name' => 'Man'), $row);
    }

    /**
     * Test Connection::fetchColumn()
     * 
     * @depends testQuery
     */
    public function testFetchColumn()
    {
        $rows = Connection::conn()->fetchColumn("SELECT id FROM foo ORDER BY id");
        $this->assertEquals(array(1, 2, 3, 4, 5), $rows);

        $rows = Connection::conn()->fetchColumn("SELECT name, id FROM foo ORDER BY id");
        $this->assertEquals(array('Foo', 'Bar', 'Zoo', 'Man', 'Ops'), $rows);

        $rows = Connection::conn()->fetchColumn("SELECT id FROM foo WHERE ext = 'n/a'");
        $this->assertEquals(array(), $rows);

        $rows = Connection::conn()->fetchColumn("SELECT name FROM foo WHERE ext = ?", 'tv');
        $this->assertEquals(array('Foo', 'Zoo'), $rows);

        $rows = Connection::conn()->fetchColumn("SELECT name FROM foo WHERE id > ? AND ext IN ?", 1, array('tv', 'rs'));
        $this->assertEquals(array('Zoo', 'Ops'), $rows);

        $rows = Connection::conn()->fetchColumn("SELECT name FROM foo WHERE id IN ?", array(1, 4));
        $this->assertEquals(array('Foo', 'Man'), $rows);

        $rows = Connection::conn()->fetchColumn("SELECT name FROM foo WHERE ext = :ext", array('ext' => 'tv'));
        $this->assertEquals(array('Foo', 'Zoo'), $rows);
    }

    /**
     * Test Connection::fetchColumn() with a result
     * 
     * @depends testQuery
     */
    public function testFetchColumn_result()
    {
        $result = Connection::conn()->query("SELECT name FROM foo ORDER BY id");
        $rows = Connection::conn()->fetchColumn($result);
        $this->assertEquals(array('Foo', 'Bar', 'Zoo', 'Man', 'Ops'), $rows);
    }

    /**
     * Test Connection::FetchPairs()
     * 
     * @depends testQuery
     */
    public function testFetchPairs()
    {
        $rows = Connection::conn()->fetchPairs("SELECT id, name FROM foo ORDER BY name");
        $this->assertEquals(array(2 => 'Bar', 1 => 'Foo', 4 => 'Man', 5 => 'Ops', 3 => 'Zoo'), $rows);

        $rows = Connection::conn()->fetchPairs("SELECT id, name FROM foo WHERE ext = 'n/a'");
        $this->assertEquals(array(), $rows);

        $rows = Connection::conn()->fetchPairs("SELECT id, name FROM foo WHERE ext = ?", 'tv');
        $this->assertEquals(array(1 => 'Foo', 3 => 'Zoo'), $rows);

        $rows = Connection::conn()->fetchPairs("SELECT id, name FROM foo WHERE id > ? AND ext IN ?", 1, array('tv', 'rs'));
        $this->assertEquals(array(3 => 'Zoo', 5 => 'Ops'), $rows);

        $rows = Connection::conn()->fetchPairs("SELECT id, name FROM foo WHERE id IN ?", array(1, 4));
        $this->assertEquals(array(1 => 'Foo', 4 => 'Man'), $rows);

        $rows = Connection::conn()->fetchPairs("SELECT id, name FROM foo WHERE ext = :ext", array('ext' => 'tv'));
        $this->assertEquals(array(1 => 'Foo', 3 => 'Zoo'), $rows);
    }

    /**
     * Test Connection::FetchPairs() with a result
     * 
     * @depends testQuery
     */
    public function testFetchPairs_result()
    {
        $result = Connection::conn()->query("SELECT id, name FROM foo ORDER BY name");
        $rows = Connection::conn()->fetchPairs($result);
        $this->assertEquals(array(2 => 'Bar', 1 => 'Foo', 4 => 'Man', 5 => 'Ops', 3 => 'Zoo'), $rows);
    }

    /**
     * Test Connection::fetchValue()
     * 
     * @depends testQuery
     */
    public function testFetchValue()
    {
        $value = Connection::conn()->fetchValue("SELECT name FROM foo WHERE id = 4");
        $this->assertEquals('Man', $value);

        $value = Connection::conn()->fetchValue("SELECT name FROM foo WHERE ext = 'n/a'");
        $this->assertNull($value);

        $value = Connection::conn()->fetchValue("SELECT name FROM foo WHERE id = ?", 4);
        $this->assertEquals('Man', $value);

        $value = Connection::conn()->fetchValue("SELECT name FROM foo WHERE id > ? AND ext IN ? ORDER BY id LIMIT 1", 1, array('tv', 'rs'));
        $this->assertEquals('Zoo', $value);

        $value = Connection::conn()->fetchValue("SELECT name FROM foo WHERE id = :id", array('id' => 4));
        $this->assertEquals('Man', $value);
    }

    /**
     * Test Connection::fetchValue() with a result
     * 
     * @depends testQuery
     */
    public function testFetchValue_result()
    {
        $result = Connection::conn()->query("SELECT name FROM foo WHERE id = 4");
        $value = Connection::conn()->fetchValue($result);
        $this->assertEquals('Man', $value);
    }

    /**
     * Test Connection::save() with a single new row of data
     * 
     * @depends testQuote
     * @depends testBackquote
     * @depends testQuery
     */
    public function testSave()
    {
        self::$reuse_db = false;

        $id = Connection::conn()->save('foo', array('name' => 'TEST', 'ext' => 'mu'));
        $this->assertEquals(6, $id);

        $result = Connection::conn()->query("SELECT * FROM foo WHERE id = 6");
        $this->assertEquals(array('id' => 6, 'name' => 'TEST', 'ext' => 'mu'), $result->fetch_assoc());
    }

    /**
     * Test Connection::save() with multiple new rows of data
     * 
     * @depends testSave
     */
    public function testSave_Rows()
    {
        self::$reuse_db = false;

        $data = array(
            array('name' => 'KLM', 'ext' => 'qq'),
            array('name' => 'NOP', 'ext' => 'tv'),
            array('ext' => 'qq', 'name' => 'QRS')
        );

        $id = Connection::conn()->save('foo', $data);
        $this->assertEquals(6, $id);

        $result = Connection::conn()->query("SELECT * FROM foo WHERE id >= 5 ORDER BY id");
        $this->assertEquals(4, $result->num_rows);
        $this->assertEquals(array('id' => 5, 'name' => 'Ops', 'ext' => 'rs'), $result->fetch_assoc());
        $this->assertEquals(array('id' => 6, 'name' => 'KLM', 'ext' => 'qq'), $result->fetch_assoc());
        $this->assertEquals(array('id' => 7, 'name' => 'NOP', 'ext' => 'tv'), $result->fetch_assoc());
        $this->assertEquals(array('id' => 8, 'name' => 'QRS', 'ext' => 'qq'), $result->fetch_assoc());
    }

    /**
     * Test Connection::save() with updates and inserts
     * 
     * @depends testSave_Rows
     */
    public function testSave_Update()
    {
        self::$reuse_db = false;

        $data = array(
            array('id' => null, 'name' => 'KLM', 'ext' => 'qq'),
            array('id' => 4, 'name' => 'MON', 'ext' => 'mu'),
            array('id' => null, 'name' => 'NOP', 'ext' => 'tv')
        );

        $id = Connection::conn()->save('foo', $data);
        $this->assertEquals(6, $id);

        $result = Connection::conn()->query("SELECT * FROM foo WHERE id >= 4 ORDER BY id");
        $this->assertEquals(4, $result->num_rows);
        $this->assertEquals(array('id' => 4, 'name' => 'MON', 'ext' => 'mu'), $result->fetch_assoc());
        $this->assertEquals(array('id' => 5, 'name' => 'Ops', 'ext' => 'rs'), $result->fetch_assoc());
        $this->assertEquals(array('id' => 6, 'name' => 'KLM', 'ext' => 'qq'), $result->fetch_assoc());
        $this->assertEquals(array('id' => 7, 'name' => 'NOP', 'ext' => 'tv'), $result->fetch_assoc());
    }

    /**
     * Test Connection::save() with ignoring existing records
     * 
     * @depends testSave_Rows
     */
    public function testSave_SkipExisting()
    {
        self::$reuse_db = false;

        $data = array(
            array('id' => null, 'name' => 'KLM', 'ext' => 'qq'),
            array('id' => 4, 'name' => 'MON', 'ext' => 'mu'),
            array('id' => null, 'name' => 'NOP', 'ext' => 'tv')
        );

        $id = Connection::conn()->save('foo', $data, Connection::SKIP_EXISTING);
        $this->assertEquals(6, $id);

        $result = Connection::conn()->query("SELECT * FROM foo WHERE id >= 4 ORDER BY id");
        $this->assertEquals(4, $result->num_rows);
        $this->assertEquals(array('id' => 4, 'name' => 'Man', 'ext' => 'mu'), $result->fetch_assoc());
        $this->assertEquals(array('id' => 5, 'name' => 'Ops', 'ext' => 'rs'), $result->fetch_assoc());
        $this->assertEquals(array('id' => 6, 'name' => 'KLM', 'ext' => 'qq'), $result->fetch_assoc());
        $this->assertEquals(array('id' => 7, 'name' => 'NOP', 'ext' => 'tv'), $result->fetch_assoc());
    }
    
    /**
     * Test Connection::setModelNamespace() and Connection::getModelNamespace()
     */
    public function testSetModelNamespace()
    {
        $this->assertNull($this->db->getModelNamespace());
        
        $this->db->setModelNamespace('Test');
        $this->assertEquals('Test', $this->db->getModelNamespace());
    }
    
    
    /**
     * Test Connection::setLogger() and Connection::logConnection
     */
    public function testLogConnection()
    {
        $message = "MySQL connection {$this->db->host_info}; thread id = {$this->db->thread_id}; version {$this->db->server_info}";
        
        $logger = $this->getMock('Psr\Log\NullLogger', array('debug'));
        $logger->expects($this->once())->method('debug')->with($this->equalTo($message));
        
        $this->db->setLogger($logger);
    }
    
    /**
     * Test Connection::setLogger() and Connection::logQuery
     */
    public function testLogQuery()
    {
        $logger = $this->getMock('Psr\Log\NullLogger', array('debug'));
        $this->db->setLogger($logger);
        
        $logger->expects($this->once())->method('debug')->with($this->stringStartsWith("SELECT * FROM foo; # 5 rows in set ("));
        $this->db->fetchAll("SELECT * FROM foo");
    }
    
    /**
     * Test Connection::setLogger() and Connection::logQuery
     */
    public function testLogQuery_Exception()
    {
        $logger = $this->getMock('Psr\Log\NullLogger', array('debug', 'error'));
        $this->db->setLogger($logger);
        
        $this->setExpectedException('Jasny\DB\MySQL\Exception');
        
        $logger->expects($this->once())->method('debug')->with($this->stringStartsWith("SELECT * FROM foobar; # ("));
        $logger->expects($this->once())->method('error')->with($this->equalTo("Table 'dbtest.foobar' doesn't exist"));
        $this->db->fetchAll("SELECT * FROM foobar");
    }
}
