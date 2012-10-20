Jasny's DB layer (for MySQL)
============================

A simple DB layer in PHP for using MySQL, featuring

* Global connection (singleton)
* Parameter binding
* Quoting tables/fields and values
* Fetch all rows, column, key/value pair and single value
* Simple saving by passing associated arrays
* Query exceptions (instead of returning false)

## Example ##

    <?php
        new DB($host, $user, $pwd, $dbname);
        $result = DB::conn()->query("SELECT * FROM foo");
        $result = DB::conn()->query("SELECT * FROM foo WHERE type = ?", $type);
        $result = DB::conn()->query("SELECT * FROM foo WHERE type = ? AND cat IN ?", $type, array(1, 7));

        $items = DB::conn()->fetchAll("SELECT id, name, description FROM foo WHERE type = ?", MYSQLI_FETCH_ASSOC, $type);
        $item  = DB::conn()->fetchOne("SELECT * FROM foo WHERE id = ?", MYSQLI_FETCH_ASSOC, $id);
        $names = DB::conn()->fetchColumn("SELECT name FROM foo WHERE type = ?", $type);
        $list  = DB::conn()->fetchPairs("SELECT id, name FROM foo WHERE type = ?", $type);
        $name  = DB::conn()->fetchValue("SELECT name FROM foo WHERE id = ?", $id);

        DB::conn()->save('foo', $values);
        DB::conn()->save('foo', array($values1, $values2, $values3));
