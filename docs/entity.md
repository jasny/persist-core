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
