# Service Locator

The `Jasny\DB` class is a [service locator][]. It grants access to registries and builders.

With the service locator your application can access any part of model from anywhere in the code, including other model
classes.

## Connection Registry

The service locator holds a single instance of a [connection registry][]. If the application has only one database
connection, it can be simply registered as 'default'.

When there are multiple connections, you can get one of the connections by name.

```php
$db = DB::conn(); // The default connection
$crm = DB::conn('crm-backend');
```

You can get the registry by using:

```php
$connections = Jasny\DB::getConnectionRegistry();
```

If needed you can use a custom registry, you can set it:

```php
$connections = new App\ConnectionRegistry();
Jasny\DB::setConnectionRegistry($connections);
```

### Configuration

To configure the connections use

```php
Jasny\DB::configure([
    'default' => [
        'driver'    => 'mysql',
        'database'  => 'database',
        'host'      => 'localhost',
        'username'  => 'root',
        'password'  => 'secure',
        'charset'   => 'utf8'
    ],
    'external' => [
        'driver'    => 'rest',
        'host'      => 'api.example.com',
        'username'  => 'user',
        'password'  => 'secure'
    ]
]);
```

This is the same as calling `Jasny\DB::getConnectionRegistry()->configure()`.

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
$mappers = new App\MapperRegistry();
Jasny\DB::setMapperRegistry($mappers);
```

## EntitySet Builder

The service locator holds a single instance of a [entity set builder][] which is returned by the `entitySet()` method.
Using the builder will clone rather than modify this instance, allow you to further specify the build.

```php
$users = Jasny\DB::entitySet()->forClass(User::class)->build($records);
```

You may set a custom entityset builder:

```php
$builder = new App\EntitySetBuilder();
Jasny\DB::setEntitySetBuilder($builder);
```


[service locator]: https://en.wikipedia.org/wiki/Service_locator_pattern
[mapper registry]: data-mapper.md#registry
[connection registry]: connection.md#registry
[entity set builder]: entity-set.md#builder

