Jasny DB layer
==============

[![Build Status](https://secure.travis-ci.org/jasny/db.png?branch=master)](http://travis-ci.org/jasny/db)

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
* Model generator


## Installation ##

Jasny DB is registred at packagist as [jasny/db](https://packagist.org/packages/jasny/db) and can be
easily installed using [composer](http://getcomposer.org/). _(recommended)_

Alternatively you can download the .zip and copy the files from the 'src' folder. In this case you also
need to download [jasny/dbquery](https://github.com/jasny/dbquery).

__Jasny DB requires at least php 5.4__

## Basic examples ##

    <?php
    use Jasny\DB\MySQL\Connection as DB;

    new DB($host, $user, $pwd, $dbname);

    $result = DB::conn()->query("SELECT * FROM foo");
    $result = DB::conn()->query("SELECT * FROM foo WHERE type = ?", $type);
    $result = DB::conn()->query("SELECT * FROM foo WHERE type = ? AND cat IN ?", $type, [1, 7]);

    $items = DB::conn()->fetchAll("SELECT id, name, description FROM foo WHERE type = ?", MYSQLI_ASSOC, $type);
    $item  = DB::conn()->fetchOne("SELECT * FROM foo WHERE id = ?", MYSQLI_ASSOC, $id);
    $names = DB::conn()->fetchColumn("SELECT name FROM foo WHERE type = ?", $type);
    $list  = DB::conn()->fetchPairs("SELECT id, name FROM foo WHERE type = ?", $type);
    $name  = DB::conn()->fetchValue("SELECT name FROM foo WHERE id = ?", $id);

    DB::conn()->save('foo', $values);
    DB::conn()->save('foo', array($values1, $values2, $values3));


## Table Gateway and Active Record ##

Jasny DB supports the [table data gateway](http://martinfowler.com/eaaCatalog/tableDataGateway.html) and
[active record](http://martinfowler.com/eaaCatalog/activeRecord.html) patterns.

    <?php
    use Jasny\DB\MySQL\Connection as DB;

    new DB($host, $user, $pwd, $dbname);

    $foo = DB::conn()->table('foo')->fetch(1);
    $foo->description = "Lorum Ipsum";
    $foo->save();


## Model generator ##

The model generator automatically generates a table gateway and active record class for each table. It does this on
demand by using the autoloader. With the model generator enabled, it's generally not needed to use the `DB->table()`
method.

    <?php
    use Jasny\DB\MySQL\Connection as DB;

    Jasny\DB\ModelGenerator::enable();
    new DB($host, $user, $pwd, $dbname);

    $foo = Foo::fetch(1);
    $foo->description = "Lorum Ipsum";
    $foo->save();

Methods called statically on record class (eg `Foo::fetch()`) are redirected to the table gateway.


## Custom Table and Record ##

To add custom methods to model classes, just create a class with the proper name (camelcase table name) and make it
extends the same class in the `Base` namespace. The model generator will create the table's class in the `Base`
namespace instead.

    <?php
    use Jasny\DB\MySQL\Connection as DB;

    Jasny\DB\ModelGenerator::enable();
    new DB($host, $user, $pwd, $dbname);

    class Foo extends DB\Foo {
        public $id;
        public $reference;
        public $description;
    }

    class FooTable extends DB\FooTable {
        public function save($record) {
            $record = (object)$record;
            if (!isset($record->reference)) $record->reference = uniqid();
            return parent::save($record);
        }

        public function fetchList() {
            return DB::fetchPairs("SELECT id, description FROM foo");
        }
    }

    $foo = new Foo();
    $foo->description = "Lorum Ipsum";
    $foo->save();

    $foo_list = Foo::fetchList();


## Query Builder ##

The query builder [jasny/dbquery](http://github.com/jasny/dbquery) is automatically included when jasny/db is installed
through composer.

    <?php
    use Jasny\DB\MySQL\Query;

    $query = Query::select()->columns(['id', 'description'])->where(['active'=>1])->limit(10);

Unlike other query builders, the Jasny DB query builder can also modify existing SQL.

    <?php
    use Jasny\DB\MySQL\Query;

    $query = new Query("SELECT * FROM foo LEFT JOIN bar ON foo.bar_id = bar.id WHERE active = 1 LIMIT 25");
    if (isset($_GET['page'])) $query->page(3);

    $query->where($_GET['filter']); // Smart quoting prevents SQL injection


## API documentation (generated) ##

http://jasny.github.com/db/docs
