<?php

/**
 * Tests for Jasny\DB\MySQL\Table.
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

/**
 * Base class for test casess for Jasny\DB\MySQL.
 * 
 * @package Test
 * @subpackage MySQL
 */
abstract class TestCase extends \PHPUnit_Framework_TestCase
{
    /**
     * @var Connection
     */
    protected $db;

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
        $m = new \mysqli(ini_get('mysqli.default_host'), ini_get('mysqli.default_user') ?: 'root', ini_get('mysqli.default_pw'));
        if ($m->connect_error) throw new \PHPUnit_Framework_SkippedTestError("Failed to connect to mysql: " . $m->connect_error);

        $sql = file_get_contents(BASE_PATH . '/tests/support/db.sql');
        if (!$m->multi_query($sql)) throw new \PHPUnit_Framework_SkippedTestError("Failed to initialise DBs: " . $m->error);

        // Make sure everything is executed
        do {
            $m->use_result();
        } while ($m->more_results() && $m->next_result());

        self::$reuse_db = true;
    }

    /**
     * Drop databases.
     * Please call dropDB if you've modified data.
     */
    protected static function dropDB()
    {
        $m = new \mysqli(ini_get('mysqli.default_host'), ini_get('mysqli.default_user') ?: 'root', ini_get('mysqli.default_pw'));
        if (!$m->connect_error) $m->query("DROP DATABASE IF EXISTS `dbtest`");
        self::$reuse_db = false;
    }

    /**
     * Close the DB connection.
     */
    protected function disconnectDB()
    {
        if (isset($this->db)) {
            $this->db->close();
            $this->db = null;
        }
    }

    /**
     * Sets up the fixture, for example, open a network connection.
     * This method is called before a test is executed.
     */
    protected function setUp()
    {
        if (!self::$reuse_db) self::createDB();

        $this->db = new Connection(ini_get('mysqli.default_host'), ini_get('mysqli.default_user') ?: 'root', ini_get('mysqli.default_pw'), 'dbtest');
    }

    /**
     * Tears down the fixture, for example, closes a network connection.
     * This method is called after a test is executed.
     */
    protected function tearDown()
    {
        $this->disconnectDB();
        \Jasny\DB\Table::$defaultConnection = null;
        
        // Clear cached table gateways
        $reflection = new \ReflectionProperty('Jasny\DB\Table', 'tables');
        $reflection->setAccessible(true);
        $reflection->setValue(null, array());
    }

    /**
     * This method is called after the last test of this test class is run.
     */
    public static function tearDownAfterClass()
    {
        self::dropDB();
    }
}
