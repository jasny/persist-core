Jasny DB
========

[![Build Status](https://secure.travis-ci.org/jasny/db.png?branch=master)](http://travis-ci.org/jasny/db)

Jasny DB adds OOP design patterns to PHP's database extensions.

* [Named connections](#named-connections)
* [Active record](#active-record)
* [Metadata](#metadata)
* [Type casting](#type-casting)
* [Validation](#validation)
* [Data mapping](#data-mapping)
* [Lazy loading](#lazy-loading)
* [Resultset](#resultset)
* [SOLID code](#solid-code)
* [Code generation](#code-generation)

Jasny DB is *not* a DB abstraction layer. It does allow you properly structure your model, while still using PHP's
native database extensions.

### Installation
The Jasny\DB library serves as an abstract base for concrete libraries implementing Jasny DB for specific
PHP extensions like mysqli and mongo. It isn't intended to be installed directly.

### Implementations

* [Jasny\DB-MySQL](http://github.com/jasny/db-mysql) extends [mysqli](http://php.net/mysqli)
* [Jasny\DB-Mongo](http://github.com/jasny/db-mongo) extends [mongo](http://php.net/mongo)


Named connections
---

By implementing the [multiton pattern](http://en.wikipedia.org/wiki/Multiton_pattern), Jasny DB enables global use 
of one or more database connections.

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
Active Record implementations have static methods for loading entities or data from the database.

* `fetch($id|$filter)` loads a single entity from the database
* `fetchAll($filter)` loads all entities from the database (optionally matching the filter)
* `fetchList($filter)` loads a list the id and description as key/value pairs (optionally matching the filter)

The `$filter` is an associated array with field name and corresponding value.

```php
$foo = Foo::fetch(10); // id = 10
$foo = Foo::fetch(['reference' => 'myfoo']);
$foos = Foo::fetchAll(['status' => 'enabled']);
$list = Foo::fetchList(['status' => 'enabled']);
```

Optinally the keys may include an operator (eg `['date <' => date('c')]`). The following operators are supported:

Operator | Description
-------- | -------------------
=        | Equals
==       | Equals (alt)
!=       | Not equals
<>       | Not equals (alt)
>        | More than
>=       | More than or equals
<        | Less than
<=       | Less than or equals
@        | Contains
!@       | Does not contain

The fetch methods are intended to support only simple cases. For specific cases you SHOULD add a specific method
and not overload the basic fetch methods.


Metadata
---

An entity represents an element in the model. The [metadata](http://en.wikipedia.org/wiki/Metadata) holds 
information about the structure of the entity. Metadata should be considered static as it describes all the
entities of a certain type.

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


Type casting
---

Entities support type casting. This is done based on the metadata. Type casting is implemented by the
[Jasny\Meta](http://www.github.com/jasny/meta) library.

### Internal types
For [php internal types](http://php.net/types) normal [type juggling](http://php.net/type-juggling) is used. Values
aren't blindly casted. For instance casting `"foo"` to an integer would trigger a warning and skip the casting.

### Objects
Casting a value to a model entity that supports [lazy loading](#lazy-loading), creates a ghost object. Entities that
implement the active record pattern, but do not support lazy loading are fetched from the database.

Casting to any other type of object will create a new object normally. For instance casting "bar" to `Foo` would 
result in `new Foo("bar")`.


Validation
---

Entities implementing the Validatable interface, can do some basic validation prior to saving them. This includes
checking that all required properties have values, checking the variable type matches and checking if values are
uniquely present in the database.


Data mapping
---

By default entity properties are mapped to fields of a single table/collection using the exact name. However it is
possible to create alernative mapping.

### Field mapping
Set the `field` in the meta data of an entity property to specify an alternative fieldname in the database. This
can be done using annotations.

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
You may save the data of a single entity across tables/collections. This allows you to further decouple the model 
from the database schema.

To do this overload the `getDBValues()` method. This method return an associated array, with the table/collection 
name as key and an array of rows (assoc arrays) that should be saved.

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


Lazy loading
---

Jasny DB supports [lazy loading](http://en.wikipedia.org/wiki/Lazy_loading) of entities by allowing them to be 
created as ghost. A ghost only hold a limited set of the entity's data, usually only the identifier. When other 
properties are accessed it will load the rest of the data.

When a value is [casted](#type-casting) to an entity that supports lazy loading, a ghost of that entity is created.


Resultset
---
_Not implemented yet_


SOLID code
---
[SOLID](http://en.wikipedia.org/wiki/SOLID_(object-oriented_design)) embodies 5 principles principles that, when 
used together, will make a code base more maintainable over time. While not forcing you to, Jasny DB supports 
building a SOLID code base.

Methods are kept small and each method is expected to be 
[overloaded](http://en.wikipedia.org/wiki/Function_overloading) by extending the class.

Functionality of Jasny DB is defined in interfaces and defined in traits around a single piece of functionality or
design pattern. The use an a specific interface will trigger behaviour. The trait may or may not be used to 
implement the interface without consequence.

To create maintainable code you SHOULD uphold the following rules:

* Don't access the database outside your model classes.
* Use traits or multiple classes to separate database logic (eg queries) from business.
* Keep the number of `if`s limited. Implement special cases by overloading.


Code generation
---

_Present in version 1, but not yet available for version 2_


API documentation (generated)
---

http://jasny.github.com/db/docs
