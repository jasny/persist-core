Jasny DB
========

[![Build Status](https://secure.travis-ci.org/jasny/db.png?branch=master)](http://travis-ci.org/jasny/db)

Jasny DB adds OOP design patterns to PHP's database extensions.

* [Named connections](#named-connections) (multiton)
* Active Record
* Metadata
* Validation and type casting
* Data Mapping
* Lazy load
* Resultset
* Separation of concerns (through [traits](http://php.net/traits))
* Code generation

Jasny DB is *not* a DB abstraction layer. It does allow you to separate business logic from database logic to create a
[SOLID](http://en.wikipedia.org/wiki/SOLID_(object-oriented_design) code base.

### Installation
The Jasny\DB library serves as an abstract base for concrete libraries implementing Jasny DB for specific
PHP extensions like mysqli and mongo. It isn't intended to be installed directly.

### Implementations

* [Jasny\DB-MySQL](http://github.com/jasny/db-mysql)
* [Jasny\DB-Mongo](http://github.com/jasny/db-mongo)


Named connections
---

By implementing the [multiton pattern](http://en.wikipedia.org/wiki/Multiton_pattern), Jasny DB enables global use of
one or more database connections.

Register connections with the `useAs($name)` method and retrieve them with the `conn($name)` method.

```php
$db = new DB();
$db->useAs('foo');

...

DB::conn('foo')->query();
```

If you only have one DB connection name it 'default', since `$name` defaults to 'default'.

```php
$db = new DB();
$db->useAs('default');

...

DB::conn()->query();
```


Active Record
---

[Enities](http://en.wikipedia.org/wiki/Entity) may be implement the
[Active Record pattern](http://en.wikipedia.org/wiki/Active_record_pattern).

### Save
Objects that implement the ActiveRecord
interface have a `save()` method for storing the entity in the database. The `setValues()` methods is a a helper
function for setting all the properties from an array and works like a
[fluent interface](http://en.wikipedia.org/wiki/Fluent_interface).

```php
$foo->setValues($data)->save();
```


### Fetch
Active Record implementations have static methods for loading entities or data from the database. The follow MUST be
implemented

`fetch($id|$filter)` | Loads a single entity from the database
`fetchAll($filter)`  | Loads all entities from the database (optionally matching the filter)
`fetchList($filter)` | Loads a list the id and description as key/value pairs (optionally matching the filter)

The `$filter` is an associated array with field name and corresponding value.

```php
$foo = Foo::fetch(10); // id = 10
$foo = Foo::fetch(['reference' => 'myfoo']);
$foos = Foo::fetchAll(['status' => 'enabled']);
$list = Foo::fetchList(['status' => 'enabled']);
```

Optinally the keys may include an operator (eg `['date <' => date('c')]`). The following operators are supported:

=  | Equals
== | Equals (alt)
!= | Not equals
<> | Not equals (alt)
>  | More than
>= | More than or equals
<  | Less than
<= | Less than or equals
@  | In
!@ | Not in

The fetch methods are intended to support only simple cases. For specific cases you SHOULD add a specific method and not
overload the basic fetch methods.


Metadata
---

An entity represents an element in the model. The [metadata](http://en.wikipedia.org/wiki/Metadata) holds information
about the structure of the entity. Metadata should be considered static as it describes all the entities of a certain
type.

Metadata for a class might contain the table name where data should be stored. Metadata for a property might contain
the data type, whether or not it is required and the property description.

Jasn DB support defining metadata through annotations by using [Jasny\Meta](http://www.github.com/jasny/meta).

```php
/**
 * Foo entity
 *
 * @collection foos
 */
class Foo
{
   /**
    * @var string
    * @required
    */
   public $color;
}
```

### Caveat
Metadata can be really powerfull in generalizing and abstracting code. However you can quickly fall into the trap of
coding through metadata. This tends to lead to code that's hard to read and maintain.

Only use the metadata to abstract widely use functionality and use overloading to implement special cases.


Validation and type casting
---

Entities implementing the Validatable interface, can do some basic validation prior to saving them. This includes
checking that all required properties have values, checking the variable type matches and checking if values are
uniquely present in the database.

Entities support type casting. This is done based on the metadata.


Data mapping
---

By default entity properties are mapped to fields of a single table/collection using the exact name. However it is
possible to create alernative mapping.

### Field mapping
Set the `field` in the meta data of an entity property to specify an alternative fieldname in the database. This can be
done using annotations.

```php
class Foo
{
   /**
    * @var Bar
    * @field bar_id
    */
   public $bar;
}
```

### Table mapping
You may save the data of a single entity across tables/collections. This allows you to further decouple the model from
the database schema.

To do this overload the `getDBValues()` method. This method return an associated array, with the table/collection name
as key and an array of rows (assoc arrays) that should be saved.

With no customization `Foo::getDBValues()` might return the following:

```php
[
  'foo' => [
    [
      'id' => null,
      'bar_id' => 20,
      'status' => 'active'
      'subs' => [
        [ 'desc' => 'red' ],
        [ 'desc' => 'green' ],
        [ 'desc' => 'blue' ]
      ];
    ]
  ]
]
```

Example of save 'subs' in a seperate table:

```php
clas Foo
{
    /**
     * Get the values to write to the database
     *
     * @return array
     */
    protected function getDBValues()
    {
        $values = parent::getDBValues();

        $subs = $values['foo'][0]['sub'];
        unset($values['foo'][0]['sub']);

        foreach ($subs as &$sub) {
            // Even if 'id' is null now, it will be set prior to saving `foo_sub`
            $sub['foo_id'] =& $values['foo'][0]['id'];
        }
        $values['foo_sub'] = $subs;

        return $values;
    }
}
```


Query object
---




API documentation (generated)
---

http://jasny.github.com/db/docs
