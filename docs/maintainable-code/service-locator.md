# Service Locator

The `Jasny\DB` class is a [service locator][]. It grants access to registries and builders.

With the service locator your application can access any part of model from anywhere in the code, including other model
classes.

## Mapper Registry

The service locator holds a single instance of a [mapper registry][]. The `map()` method is a shortcut for creating a
mapper.

```php
$users = Jasny\DB::map(User::class)->fetchAll();
```

If you need direct access to the mapper you can use

```php
$mappers = Jasny\DB::getMapperRegistry();
```

To use a custom mapper registry use

```php
$mappers = new MyCustomMapperRegistry();
Jasny\DB::setMapperRegistry($mappers);
```

## Connection Registry

The service locator holds a single instance of a [connection registry][]. If the application has only one database
connection, it can be simply registered as 'default'.

When there are multiple connections, you can get one of the connections by name.

```php
$db = DB::conn(); // The default connection
$crm = DB::conn('crm-backend');
```

To use a custom connection registry use

```php
$factory = new Jasny\DB\ConnectionFactory();
Jasny\DB::withConnectionRegistry(new MyCustomConnectionRegistry($factory));
```

> **Note:** Using the database connection should be reserved to classes that have the specific function to interact
> with the databases, like a data mapper or data import class.

## EntitySet Builder

The service locator holds a single instance of a [entity set builder][]. The `entitySet()` method will clone this
instance, allow you to further specify the build.

```php
$users = Jasny\DB::entitySet()->forClass(User::class)->allowDuplicates()->create($records);
```

```php
$builder = new MyCustomEntitySetBuilder();
Jasny\DB::withEntitySetBuilder($builder);
```


[service locator]: https://en.wikipedia.org/wiki/Service_locator_pattern
[mapper registry]: ../data-mapper/index.md#registry
[connection registry]: ../connection/index.md#registry
[entity set builder]: ../entity-set/index.md#factory
---

[Next: Connection »](connection/index.md)

