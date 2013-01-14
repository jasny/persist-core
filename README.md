Jasny's DB layer (for MySQL)
============================

[![Build Status](https://secure.travis-ci.org/jasny/DB-MySQL.png?branch=master)](http://travis-ci.org/jasny/DB-MySQL)

A simple DB layer in PHP for using MySQL, featuring

* Global connection (singleton)
* Parameter binding
* Quoting tables/fields and values
* Fetch all rows, column, key/value pair and single value
* Simple saving by passing associated arrays
* Query exceptions (instead of returning false)

## Installation ##

Jasny DB-MySQL is registred at packagist as [jasny/db-mysql](https://packagist.org/packages/jasny/db-mysql) and can be
easily installed using [composer](http://getcomposer.org/). Alternatively you can simply download the .zip and copy
the file from the 'src' folder.

## Example ##

    <?php
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

## API documentation (generated) ##

http://jasny.github.com/DB-MySQL/docs
