<?php

/**
 * Tests for DB.
 * 
 * Please GRANT ALL PRIVILEGES ON dbtest.* TO dbtest@localhost IDENTIFIED BY "dbtest1";
 * 
 * @author Arnold Daniels
 */
/** */
require_once 'PHPUnit/Framework/TestCase.php';

require_once __DIR__ . '/../src/DB.php';

/**
 * Tests for DB.
 * 
 * @package Test
 * @subpackage DB
 */
class DBTest extends PHPUnit_Framework_TestCase
{

    /**
     * @var DB
     */
    private $db;

    /**
     * Should I reuse the created DBs
     * @var boolean
     */
    protected static $reuse_db = false;

    /**
     * Create new databases.
     */
    protected static function createDB()
    {
        // Setup DB
        $m = @new mysqli('localhost', 'dbtest', 'dbtest1');
        if ($m->connect_error) throw new PHPUnit_Framework_SkippedTestError("Failed to connect to mysql: " . $m->connect_error);

        $sql = file_get_contents(__DIR__ . '/support/db.sql');
        if (!$m->multi_query($sql)) throw new PHPUnit_Framework_SkippedTestError("Failed to initialise DBs: " . $m->error);

        // Make sure everything is executed
        do {
            $m->use_result();
        } while ($m->next_result());

        self::$reuse_db = true;
    }

    /**
     * Drop databases.
     * Please call dropDB if you've modified data.
     */
    protected static function dropDB()
    {
        $m = @new mysqli('localhost', 'dbtest', 'dbtest1');
        if (!$m->connect_error) $m->query("DROP DATABASE IF EXISTS `dbtest`");
        self::$reuse_db = false;
    }

    /**
     * Disconnect from the DB by unsetting the connection.
     */
    protected function disconnectDB()
    {
        // Clear DB connection
        $refl = new ReflectionClass('DB');
        $prop = $refl->getProperty('connection');
        $prop->setAccessible(true);
        $prop->setValue(null, null);

        $this->db = null;
    }

    /**
     * Sets up the fixture, for example, open a network connection.
     * This method is called before a test is executed.
     */
    protected function setUp()
    {
        if (!self::$reuse_db) self::createDB();

        $this->db = new DB('localhost', 'dbtest', 'dbtest1', 'dbtest');
    }

    /**
     * Tears down the fixture, for example, closes a network connection.
     * This method is called after a test is executed.
     */
    protected function tearDown()
    {
        $this->disconnectDB();
    }

    /**
     * This method is called after the last test of this test class is run.
     */
    public static function tearDownAfterClass()
    {
        self::dropDB();
    }

    /**
     * Test DB::conn()
     */
    public function testConn()
    {
        $conn = DB::conn();
        $this->assertInstanceOf('DB', $conn);
        $this->assertSame($this->db, $conn);

        list($db) = mysqli_query($conn, "SELECT DATABASE()")->fetch_row();
        $this->assertEquals('dbtest', $db);

        $this->assertSame($conn, DB::conn());
    }

    /**
     * Test DB::conn() for an unexisting connection
     */
    public function testConn_Fail()
    {
        $this->setExpectedException('DB_Exception', "Unable to create DB connection: not configured");

        $this->disconnectDB();
        DB::conn();
    }

    /**
     * Test DB::quote()
     */
    public function testQuote()
    {
        $this->assertEquals('TRUE', DB::quote(true));
        $this->assertEquals('FALSE', DB::quote(false));

        $this->assertEquals('NULL', DB::quote(null));
        $this->assertEquals('DEFAULT', DB::quote(null, 'DEFAULT'));

        $this->assertEquals('10', DB::quote(10));
        $this->assertEquals('-20', DB::quote(-20));
        $this->assertEquals('3.1415', DB::quote(3.1415));

        $this->assertEquals('"jan"', DB::quote('jan'));
        $this->assertEquals('"Quoting \"It\'s Fantastic\""', DB::quote('Quoting "It\'s Fantastic"'));
        $this->assertEquals('"Multi\nline\ntext"', DB::quote("Multi\nline\ntext"));
        $this->assertEquals('"Yet\\\\Another\\\\Namespace"', DB::quote('Yet\Another\Namespace')); // 2 escaped backslases = 4 backslashes

        $this->assertEquals('(10, 20, 99)', DB::quote(array(10, 20, 99)));
        $this->assertEquals('(10, NULL, "jan", TRUE)', DB::quote(array(10, null, 'jan', true)));
    }

    /**
     * Test DB::backquote()
     */
    public function testBackquote()
    {
        $this->assertEquals('`name`', DB::backquote('name'));
        $this->assertEquals('`name with spaces`', DB::backquote('name with spaces'));
        $this->assertEquals('`name, password`', DB::backquote('name`, `password'));
    }

    /**
     * Test DB::bind() with basic placeholders
     * 
     * @depends testQuote
     */
    public function testBind()
    {
        $query = DB::bind('SELECT * FROM foo');
        $this->assertEquals('SELECT * FROM foo', $query);

        $query = DB::bind('SELECT * FROM foo WHERE id = ?', 10);
        $this->assertEquals('SELECT * FROM foo WHERE id = 10', $query);

        $query = DB::bind('SELECT * FROM foo WHERE name = ?', 'jan');
        $this->assertEquals('SELECT * FROM foo WHERE name = "jan"', $query);

        $query = DB::bind('SELECT * FROM foo WHERE active = ?', true);
        $this->assertEquals('SELECT * FROM foo WHERE active = TRUE', $query);

        $query = DB::bind('SELECT *, "a\"b?d" AS `str?` FROM foo WHERE id = ?', 10);
        $this->assertEquals('SELECT *, "a\"b?d" AS `str?` FROM foo WHERE id = 10', $query);

        $query = DB::bind('SELECT * FROM foo WHERE id = ? AND active = ? LIMIT ?', 10, true, 15);
        $this->assertEquals('SELECT * FROM foo WHERE id = 10 AND active = TRUE LIMIT 15', $query);

        $query = DB::bind('UPDATE foo SET data = ? WHERE id = ?', null, 10);
        $this->assertEquals('UPDATE foo SET data = NULL WHERE id = 10', $query);

        $query = DB::bind('SELECT * FROM foo WHERE id = ? AND active = ?', 10);
        $this->assertEquals('SELECT * FROM foo WHERE id = 10 AND active = ?', $query);

        $query = DB::bind('SELECT * FROM foo WHERE id IN ?', array(10, 20, 99));
        $this->assertEquals('SELECT * FROM foo WHERE id IN (10, 20, 99)', $query);
    }

    /**
     * Test DB::bind() with named placeholders
     * 
     * @depends testQuote
     */
    public function testBind_Named()
    {
        $query = DB::bind('SELECT * FROM foo WHERE id = :id', array('id' => 10));
        $this->assertEquals('SELECT * FROM foo WHERE id = 10', $query);

        $query = DB::bind('SELECT * FROM foo WHERE name = :name', array('name' => 'jan'));
        $this->assertEquals('SELECT * FROM foo WHERE name = "jan"', $query);

        $query = DB::bind('SELECT *, "a\"b:id" AS `str:id` FROM foo WHERE id = :id', array('id' => 10));
        $this->assertEquals('SELECT *, "a\"b:id" AS `str:id` FROM foo WHERE id = 10', $query);

        $query = DB::bind('SELECT * FROM foo WHERE id = :id AND active = :active LIMIT :limit', array('id' => 10, 'active' => true, 'limit' => 15));
        $this->assertEquals('SELECT * FROM foo WHERE id = 10 AND active = TRUE LIMIT 15', $query);

        $query = DB::bind('UPDATE foo SET data = :data WHERE id = :id', array('data' => null, 'id' => 10));
        $this->assertEquals('UPDATE foo SET data = NULL WHERE id = 10', $query);

        $query = DB::bind('SELECT * FROM foo WHERE id = :id AND active = :active', array('id' => 10));
        $this->assertEquals('SELECT * FROM foo WHERE id = 10 AND active = :active', $query);

        $query = DB::bind('SELECT * FROM foo WHERE id IN :ids', array('ids' => array(10, 20, 99)));
        $this->assertEquals('SELECT * FROM foo WHERE id IN (10, 20, 99)', $query);
    }

    /**
     * Test DB::query() with basic placeholders
     * 
     * @depends testConn
     * @depends testBind
     */
    public function testQuery()
    {
        $result = DB::conn()->query("SELECT id, name FROM foo ORDER BY id");
        $this->assertInstanceOf('mysqli_result', $result);
        $this->assertEquals(5, $result->num_rows);
        $this->assertEquals(array('id' => 1, 'name' => 'Foo'), $result->fetch_assoc());
        // No need to check all rows

        $result = DB::conn()->query("SELECT id FROM foo WHERE ext = 'n/a'");
        $this->assertInstanceOf('mysqli_result', $result);
        $this->assertEquals(0, $result->num_rows);

        $result = DB::conn()->query("SELECT id, name FROM foo WHERE id = ?", 4);
        $this->assertInstanceOf('mysqli_result', $result);
        $this->assertEquals(1, $result->num_rows);
        $this->assertEquals(array('id' => 4, 'name' => 'Man'), $result->fetch_assoc());

        $result = DB::conn()->query("SELECT id, name FROM foo WHERE id > ? AND ext = ?", 1, 'tv');
        $this->assertInstanceOf('mysqli_result', $result);
        $this->assertEquals(1, $result->num_rows);
        $this->assertEquals(array('id' => 3, 'name' => 'Zoo'), $result->fetch_assoc());

        $result = DB::conn()->query("SELECT id, name FROM foo WHERE id IN ?", array(1, 4));
        $this->assertInstanceOf('mysqli_result', $result);
        $this->assertEquals(2, $result->num_rows);
        $this->assertEquals(array('id' => 1, 'name' => 'Foo'), $result->fetch_assoc());
        $this->assertEquals(array('id' => 4, 'name' => 'Man'), $result->fetch_assoc());

        $result = DB::conn()->query('SELECT id, name FROM foo WHERE id = :id', array('id' => 4));
        $this->assertEquals(1, $result->num_rows);
        $this->assertEquals(array('id' => 4, 'name' => 'Man'), $result->fetch_assoc());
    }

    /**
     * Test DB::query() with named placeholders
     * 
     * @depends testConn
     * @depends testBind_Named
     */
    public function testQuery_Named()
    {
        $result = DB::conn()->query("SELECT id, name FROM foo WHERE id > :id AND ext = :ext", array('id' => 1, 'ext' => 'tv'));
        $this->assertInstanceOf('mysqli_result', $result);
        $this->assertEquals(1, $result->num_rows);
        $this->assertEquals(array('id' => 3, 'name' => 'Zoo'), $result->fetch_assoc());
    }

    /**
     * Test DB::query() with an update query
     * 
     * @depends testConn
     */
    public function testQuery_Write()
    {
        self::$reuse_db = false;

        $result = DB::conn()->query("UPDATE foo SET name='TEST' WHERE ext='tv'");
        $this->assertTrue($result);
        $this->assertEquals(2, DB::conn()->affected_rows);

        $result = DB::conn()->query("SELECT id, name FROM foo ORDER BY id");
        $this->assertInstanceOf('mysqli_result', $result);
        $this->assertEquals(5, $result->num_rows);
        $this->assertEquals(array('id' => 1, 'name' => 'TEST'), $result->fetch_assoc());
        $this->assertEquals(array('id' => 2, 'name' => 'Bar'), $result->fetch_assoc());
    }

    /**
     * Test DB_Exception by trying to execute and invalid query
     * 
     * @depends testQuery
     */
    public function testQuery_DBException()
    {
        $query = "SELECT * FROM foobar";

        try {
            DB::conn()->query($query);
            $this->fail("No DB_Exception was thrown");
        } catch (DB_Exception $e) {
            
        }

        $this->assertInstanceOf('DB_Exception', $e);
        $this->assertEquals($query, $e->getQuery());
        $this->assertEquals(1146, $e->getCode());
        $this->assertEquals("Table 'dbtest.foobar' doesn't exist", $e->getError());
        $this->assertEquals("Query has failed. Table 'dbtest.foobar' doesn't exist.\n$query", $e->getMessage());
    }

    /**
     * Test DB::fetchAll()
     * 
     * @depends testQuery
     */
    public function testFetchAll()
    {
        $rows = DB::conn()->fetchAll("SELECT id, name FROM foo ORDER BY id");
        $this->assertEquals(array(array('id' => 1, 'name' => 'Foo'), array('id' => 2, 'name' => 'Bar'), array('id' => 3, 'name' => 'Zoo'), array('id' => 4, 'name' => 'Man'), array('id' => 5, 'name' => 'Ops')), $rows);

        $rows = DB::conn()->fetchAll("SELECT id, name FROM foo ORDER BY id", MYSQLI_NUM);
        $this->assertEquals(array(array(1, 'Foo'), array(2, 'Bar'), array(3, 'Zoo'), array(4, 'Man'), array(5, 'Ops')), $rows);

        $rows = DB::conn()->fetchAll("SELECT id, name FROM foo ORDER BY id", MYSQLI_BOTH);
        $this->assertEquals(array(array(1, 'Foo', 'id' => 1, 'name' => 'Foo'), array(2, 'Bar', 'id' => 2, 'name' => 'Bar'), array(3, 'Zoo', 'id' => 3, 'name' => 'Zoo'), array(4, 'Man', 'id' => 4, 'name' => 'Man'), array(5, 'Ops', 'id' => 5, 'name' => 'Ops')), $rows);

        $rows = DB::conn()->fetchAll("SELECT id FROM foo WHERE ext = 'n/a'");
        $this->assertEquals(array(), $rows);

        $rows = DB::conn()->fetchAll("SELECT id, name FROM foo WHERE ext = ?", MYSQLI_ASSOC, 'tv');
        $this->assertEquals(array(array('id' => 1, 'name' => 'Foo'), array('id' => 3, 'name' => 'Zoo')), $rows);

        $rows = DB::conn()->fetchAll("SELECT id, name FROM foo WHERE id > ? AND ext IN ?", MYSQLI_ASSOC, 1, array('tv', 'rs'));
        $this->assertEquals(array(array('id' => 3, 'name' => 'Zoo'), array('id' => 5, 'name' => 'Ops')), $rows);

        $rows = DB::conn()->fetchAll("SELECT id, name FROM foo WHERE id IN ?", MYSQLI_ASSOC, array(1, 4));
        $this->assertEquals(array(array('id' => 1, 'name' => 'Foo'), array('id' => 4, 'name' => 'Man')), $rows);

        $rows = DB::conn()->fetchAll("SELECT id, name FROM foo WHERE ext = :ext", MYSQLI_ASSOC, array('ext' => 'tv'));
        $this->assertEquals(array(array('id' => 1, 'name' => 'Foo'), array('id' => 3, 'name' => 'Zoo')), $rows);
    }

    /**
     * Test DB::fetchAll() with a result
     * 
     * @depends testQuery
     */
    public function testFetchAll_result()
    {
        $result = DB::conn()->query("SELECT id, name FROM foo ORDER BY id");
        $rows = DB::conn()->fetchAll($result);
        $this->assertEquals(array(array('id' => 1, 'name' => 'Foo'), array('id' => 2, 'name' => 'Bar'), array('id' => 3, 'name' => 'Zoo'), array('id' => 4, 'name' => 'Man'), array('id' => 5, 'name' => 'Ops')), $rows);
    }

    /**
     * Test DB::fetchColumn()
     * 
     * @depends testQuery
     */
    public function testFetchColumn()
    {
        $rows = DB::conn()->fetchColumn("SELECT id FROM foo ORDER BY id");
        $this->assertEquals(array(1, 2, 3, 4, 5), $rows);

        $rows = DB::conn()->fetchColumn("SELECT name, id FROM foo ORDER BY id");
        $this->assertEquals(array('Foo', 'Bar', 'Zoo', 'Man', 'Ops'), $rows);

        $rows = DB::conn()->fetchColumn("SELECT id FROM foo WHERE ext = 'n/a'");
        $this->assertEquals(array(), $rows);

        $rows = DB::conn()->fetchColumn("SELECT name FROM foo WHERE ext = ?", 'tv');
        $this->assertEquals(array('Foo', 'Zoo'), $rows);

        $rows = DB::conn()->fetchColumn("SELECT name FROM foo WHERE id > ? AND ext IN ?", 1, array('tv', 'rs'));
        $this->assertEquals(array('Zoo', 'Ops'), $rows);

        $rows = DB::conn()->fetchColumn("SELECT name FROM foo WHERE id IN ?", array(1, 4));
        $this->assertEquals(array('Foo', 'Man'), $rows);

        $rows = DB::conn()->fetchColumn("SELECT name FROM foo WHERE ext = :ext", array('ext' => 'tv'));
        $this->assertEquals(array('Foo', 'Zoo'), $rows);
    }

    /**
     * Test DB::fetchColumn() with a result
     * 
     * @depends testQuery
     */
    public function testFetchColumn_result()
    {
        $result = DB::conn()->query("SELECT name FROM foo ORDER BY id");
        $rows = DB::conn()->fetchColumn($result);
        $this->assertEquals(array('Foo', 'Bar', 'Zoo', 'Man', 'Ops'), $rows);
    }

    /**
     * Test DB::FetchPairs()
     * 
     * @depends testQuery
     */
    public function testFetchPairs()
    {
        $rows = DB::conn()->fetchPairs("SELECT id, name FROM foo ORDER BY name");
        $this->assertEquals(array(2 => 'Bar', 1 => 'Foo', 4 => 'Man', 5 => 'Ops', 3 => 'Zoo'), $rows);

        $rows = DB::conn()->fetchPairs("SELECT id, name FROM foo WHERE ext = 'n/a'");
        $this->assertEquals(array(), $rows);

        $rows = DB::conn()->fetchPairs("SELECT id, name FROM foo WHERE ext = ?", 'tv');
        $this->assertEquals(array(1 => 'Foo', 3 => 'Zoo'), $rows);

        $rows = DB::conn()->fetchPairs("SELECT id, name FROM foo WHERE id > ? AND ext IN ?", 1, array('tv', 'rs'));
        $this->assertEquals(array(3 => 'Zoo', 5 => 'Ops'), $rows);

        $rows = DB::conn()->fetchPairs("SELECT id, name FROM foo WHERE id IN ?", array(1, 4));
        $this->assertEquals(array(1 => 'Foo', 4 => 'Man'), $rows);

        $rows = DB::conn()->fetchPairs("SELECT id, name FROM foo WHERE ext = :ext", array('ext' => 'tv'));
        $this->assertEquals(array(1 => 'Foo', 3 => 'Zoo'), $rows);
    }

    /**
     * Test DB::FetchPairs() with a result
     * 
     * @depends testQuery
     */
    public function testFetchPairs_result()
    {
        $result = DB::conn()->query("SELECT id, name FROM foo ORDER BY name");
        $rows = DB::conn()->fetchPairs($result);
        $this->assertEquals(array(2 => 'Bar', 1 => 'Foo', 4 => 'Man', 5 => 'Ops', 3 => 'Zoo'), $rows);
    }

    /**
     * Test DB::fetchValue()
     * 
     * @depends testQuery
     */
    public function testFetchValue()
    {
        $value = DB::conn()->fetchValue("SELECT name FROM foo WHERE id = 4");
        $this->assertEquals('Man', $value);

        $value = DB::conn()->fetchValue("SELECT name FROM foo WHERE ext = 'n/a'");
        $this->assertNull($value);

        $value = DB::conn()->fetchValue("SELECT name FROM foo WHERE id = ?", 4);
        $this->assertEquals('Man', $value);

        $value = DB::conn()->fetchValue("SELECT name FROM foo WHERE id > ? AND ext IN ? ORDER BY id LIMIT 1", 1, array('tv', 'rs'));
        $this->assertEquals('Zoo', $value);

        $value = DB::conn()->fetchValue("SELECT name FROM foo WHERE id = :id", array('id' => 4));
        $this->assertEquals('Man', $value);
    }

    /**
     * Test DB::fetchValue() with a result
     * 
     * @depends testQuery
     */
    public function testFetchValue_result()
    {
        $result = DB::conn()->query("SELECT name FROM foo WHERE id = 4");
        $value = DB::conn()->fetchValue($result);
        $this->assertEquals('Man', $value);
    }

    /**
     * Test DB::save() with a single new row of data
     * 
     * @depends testQuote
     * @depends testBackquote
     * @depends testQuery
     */
    public function testSave()
    {
        self::$reuse_db = false;

        $id = DB::conn()->save('foo', array('name' => 'TEST', 'ext' => 'mu'));
        $this->assertEquals(6, $id);

        $result = DB::conn()->query("SELECT * FROM foo WHERE id = 6");
        $this->assertEquals(array('id' => 6, 'name' => 'TEST', 'ext' => 'mu'), $result->fetch_assoc());
    }

    /**
     * Test DB::save() with multiple new rows of data
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

        $id = DB::conn()->save('foo', $data);
        $this->assertEquals(6, $id);

        $result = DB::conn()->query("SELECT * FROM foo WHERE id >= 5 ORDER BY id");
        $this->assertEquals(4, $result->num_rows);
        $this->assertEquals(array('id' => 5, 'name' => 'Ops', 'ext' => 'rs'), $result->fetch_assoc());
        $this->assertEquals(array('id' => 6, 'name' => 'KLM', 'ext' => 'qq'), $result->fetch_assoc());
        $this->assertEquals(array('id' => 7, 'name' => 'NOP', 'ext' => 'tv'), $result->fetch_assoc());
        $this->assertEquals(array('id' => 8, 'name' => 'QRS', 'ext' => 'qq'), $result->fetch_assoc());
    }

    /**
     * Test DB::save() with updates and inserts
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

        $id = DB::conn()->save('foo', $data);
        $this->assertEquals(6, $id);

        $result = DB::conn()->query("SELECT * FROM foo WHERE id >= 4 ORDER BY id");
        $this->assertEquals(4, $result->num_rows);
        $this->assertEquals(array('id' => 4, 'name' => 'MON', 'ext' => 'mu'), $result->fetch_assoc());
        $this->assertEquals(array('id' => 5, 'name' => 'Ops', 'ext' => 'rs'), $result->fetch_assoc());
        $this->assertEquals(array('id' => 6, 'name' => 'KLM', 'ext' => 'qq'), $result->fetch_assoc());
        $this->assertEquals(array('id' => 7, 'name' => 'NOP', 'ext' => 'tv'), $result->fetch_assoc());
    }

    /**
     * Test DB::save() with ignoring existing records
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

        $id = DB::conn()->save('foo', $data, DB::SKIP_EXISTING);
        $this->assertEquals(6, $id);

        $result = DB::conn()->query("SELECT * FROM foo WHERE id >= 4 ORDER BY id");
        $this->assertEquals(4, $result->num_rows);
        $this->assertEquals(array('id' => 4, 'name' => 'Man', 'ext' => 'mu'), $result->fetch_assoc());
        $this->assertEquals(array('id' => 5, 'name' => 'Ops', 'ext' => 'rs'), $result->fetch_assoc());
        $this->assertEquals(array('id' => 6, 'name' => 'KLM', 'ext' => 'qq'), $result->fetch_assoc());
        $this->assertEquals(array('id' => 7, 'name' => 'NOP', 'ext' => 'tv'), $result->fetch_assoc());
    }

}
