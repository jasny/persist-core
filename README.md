Jasny's DB layer
================

[![Build Status](https://secure.travis-ci.org/jasny/DB-MySQL.png?branch=master)](http://travis-ci.org/jasny/DB-MySQL)

A full featured and easy to use DB layer in PHP, featuring

* MySQL support only (for now)
* Global connection (singleton)
* Parameter binding
* Quoting tables/fields and values
* Fetch all rows, column, key/value pair and single value
* Simple saving by passing associated arrays
* Query exceptions (instead of returning false)
* Table Gateway
* Active Record

## Installation ##

Jasny DB is registred at packagist as [jasny/db](https://packagist.org/packages/jasny/db) and can be
easily installed using [composer](http://getcomposer.org/). Alternatively you can simply download the .zip and copy
the file from the 'src' folder.

## Basic examples ##

    <?php
    use \Jasny\DB\MySQL\Connection as DB;

    new DB($host, $user, $pwd, $dbname);

    $result = DB::conn()->query("SELECT * FROM foo");
    $result = DB::conn()->query("SELECT * FROM foo WHERE type = ?", $type);
    $result = DB::conn()->query("SELECT * FROM foo WHERE type = ? AND cat IN ?", $type, array(1, 7));

    $items = DB::conn()->fetchAll("SELECT id, name, description FROM foo WHERE type = ?", MYSQLI_ASSOC, $type);
    $item  = DB::conn()->fetchOne("SELECT * FROM foo WHERE id = ?", MYSQLI_ASSOC, $id);
    $names = DB::conn()->fetchColumn("SELECT name FROM foo WHERE type = ?", $type);
    $list  = DB::conn()->fetchPairs("SELECT id, name FROM foo WHERE type = ?", $type);
    $name  = DB::conn()->fetchValue("SELECT name FROM foo WHERE id = ?", $id);

    DB::conn()->save('foo', $values);
    DB::conn()->save('foo', array($values1, $values2, $values3));


## Table Gateway and Active Record ##

    <?php
    use \Jasny\DB\MySQL\Connection as DB;

    new DB($host, $user, $pwd, $dbname);

    $foo = DB::conn()->table('foo')->load(1);
    $foo->description = "Lorum Ipsum";
    $foo->save();


## Custom Table and Record ##

    <?php
    use \Jasny\DB\MySQL\Connection as DB;

    class Foo extends \Jasny\DB\Record {
        public $id;
        public $reference;
        public $description;
    }

    class FooTable extends \Jasny\DB\MySQL\Table {
        public function save($record) {
            $record = (object)$record;
            if (!isset($record->reference)) $record->reference = uniqid();
            return parent::save($record);
        }
    }

    new DB($host, $user, $pwd, $dbname);

    $foo = Foo::load(1);
    $foo->description = "Lorum Ipsum";
    $foo->save();


## API documentation (generated) ##

http://jasny.github.com/db/docs
