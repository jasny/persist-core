Jasny DB
========

[![Build Status](https://secure.travis-ci.org/jasny/db.png?branch=master)](http://travis-ci.org/jasny/db)

Jasny DB adds OOP design patterns to PHP's database extensions.

* [Named connections](#named-connections)
* [Entity](#entity)
* [Active record](#active-record)
* [Data mapper](#data-mapper)
* [Recordset](#recordset)
* [Validation](#validation)
* [Lazy loading](#lazy-loading)
* [Soft deletion](#soft-deletion)
* [Resultset](#resultset)
* [Maintainable code](#maintainable-code)
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


Entity
---

An entity is a "thing" you want to represent in a database or other data storages. It can be a new article on your
blog, a user in your message board or a permission in your rights management system.

The properties of an entity object is a representation of the data. Entities usually also carry business logic.

### Set values
The `setValues()` methods is a a helper function for setting all the properties from an array and works like a
[fluent interface](http://en.wikipedia.org/wiki/Fluent_interface).

```php
$foo = new Foo();
$foo->setValues(['red' => 10, 'green' => 20, 'blue' => 30])->doSomething();
```

### Instantiation
Using the `new` keyword is reserved for creating a new entity.

When the data of an entity is fetched, the `__set_state()` method is used to create the entity. This method sets the
properties of the entity object before calling the constructor.


Active Record
---

Enities may be implement the [Active Record pattern](http://en.wikipedia.org/wiki/Active_record_pattern). Active records
combine data and database access in a single object.

### Fetch
An entity can be loaded from the database using the `fetch($id)`.

```php
$foo = Foo::fetch(10); // id = 10
$foo = Foo::fetch(['reference' => 'myfoo']);
```

### Save
Objects that implement the ActiveRecord interface have a `save()` method for storing the entity in the database.

```php
$foo->save();
$foo->setValues($data)->save();
```

### Delete
Entities may be removed from the database using the `delete()` method.

```php
$foo->delete();
```

Optionally [soft deletion](#soft-deletion) can be implemented, so deleted entities can be restored.

```php
$foo->undelete();
```


Data Mapper
---

You may choose to separate database logic from business logic by using a
[Data Mapper](http://en.wikipedia.org/wiki/Data_mapper_pattern). The Data Mapper is responsible for loading entities
from and storing them to their database.

You should either use Data Mappers or Active Record, not both. When using Data Mappers, the entities should not be
aware of the database and contain no database code (eg SQL queries).

### Fetch
An entity can be loaded from the database using the `fetch($id)`.

```php
$foo = FooMapper::fetch(10); // id = 10
$foo = FooMapper::fetch(['reference' => 'myfoo']);
```

### Save
To store entities a Data Mapper implements the `save($entity)` method.

```php
FooMapper::save($foo);
FooMapper::save($foo->setValues($data));
```

### Delete
Entities may be removed from the database using the `delete($entity)` method.

```php
FooMapper::delete($foo);
```

Optionally [soft deletion](#soft-deletion) can be implemented, so deleted entities can be restored.

```php
FooMapper::undelete($foo);
```


Recordset
---

An entity tends to be a part of a set of data, like a table or collection. If it's possible to load multiple entities
from that set, the Active Record or Data Mapper implements the Recordset interface.

The `fetch()` method returns a single entity. The `fetchAll()` method returns multiple enities. `fetchList()`
loads a list with the id and description as key/value pairs. The `count()` method counts the number of entities in the
set.

Each of these methods accept a `$filter` argument. The filter is an associated array with field name and corresponding
value. _Note that the `fetch()` methods takes either a unique ID or filter._

```php
$foo   = Foo::fetch(['reference' => 'zoo']);
$foos  = Foo::fetchAll(['bar' => 10]);
$list  = Foo::fetchList(['bar' => 10]);
$count = Foo::count(['bar' => 10]);
```

Optinally filter keys may include an operator (eg `['date <' => date('c')]`). The following operators are supported:

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


Validation
---

Entities implementing the Validatable interface, can do some basic validation prior to saving them. This includes
checking that all required properties have values, checking the variable type matches and checking if values are
uniquely present in the database.


Lazy loading
---

Jasny DB supports [lazy loading](http://en.wikipedia.org/wiki/Lazy_loading) of entities by allowing them to be 
created as ghost. A ghost only hold a limited set of the entity's data, usually only the identifier. When other 
properties are accessed it will load the rest of the data.

When a value is [casted](#type-casting) to an entity that supports lazy loading, a ghost of that entity is created.


Soft deletion
---

Entities that support soft deletion are deleted in such a way that they can restored.

Deleted entities may restored using `undelete()` or they can be permanently removed using `purge()`.

The `isDeleted()` method check whether this document has been deleted.

Fetch methods do not return deleted entities. Instead use `fetchDeleted($filter)` to load a deleted entity. Use
`fetchAllDeleted($filter)` to fetch all deleted entities from the database.


Resultset
---

_Not implemented yet_


Maintainable code
---

To create maintainable code you SHOULD at least uphold the following rules:

* Don't access the database outside your model classes.
* Use traits or multiple classes to separate database logic (eg queries) from business.
* Keep the number of `if`s limited. Implement special cases by overloading.

### SOLID
[SOLID](http://en.wikipedia.org/wiki/SOLID_(object-oriented_design)) embodies 5 principles principles that, when 
used together, will make a code base more maintainable over time. While not forcing you to, Jasny DB supports 
building a SOLID code base.

Methods are kept small and each method is expected to be 
[overloaded](http://en.wikipedia.org/wiki/Function_overloading) by extending the class.

Functionality of Jasny DB is defined in interfaces and defined in traits around a single piece of functionality or
design pattern. The use an a specific interface will trigger behaviour. The trait may or may not be used to 
implement the interface without consequence.

### Active Record and SRP
Using the Active Records pattern is considered breaking the
[Single responsibility principle](http://en.wikipedia.org/wiki/Single_responsibility_principle). Active records tend to
combine database logic with business logic a single class.

However this pattern produces code that is more readable and easier to understand. For that reason it remains very
popular.

In the end the choice is up to you. Using the Active Record pattern is always optional with Jasny DB. Alternatively you
may choose to use Data Mapper for database interaction.

```php
// Active Record
$user = User::fetch(10);
$user->setValues($data);
$user->save();

// Data Mapper
$user = UserMapper::fetch(10);
$user->setValues($data);
UserMapper::save($user);
```


Code generation
---

_Present in version 1, but not yet available for version 2_


API documentation (generated)
---

http://jasny.github.com/db/docs
