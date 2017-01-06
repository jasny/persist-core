# Connections

Connection objects are use used to interact with a database. Other object must use a connection object do actions like
getting getting, saving and deleting data from the DB.

Connection objects should extends a native database connection object. Only the constructor is defined in the
`Connection` interface, all other methods are inherited.

```php
$db = new Jasny\DB\MySQL\Connection([
    'database'  => 'database',
    'host'      => 'localhost',
    'username'  => 'root',
    'password'  => 'secure',
    'charset'   => 'utf8'
]);
```


## Factory

A `ConnectionFactory` allows the creation of a DB connection by specifying a driver name.

The `make()` method allows you to create a new connection specifying the driver and DB settings.

```php
$factory = new Jasny\DB\Base\ConnectionFactory();

$db = $factory->make('mysql', [
    'database'  => 'database',
    'host'      => 'localhost',
    'username'  => 'root',
    'password'  => 'secure',
    'charset'   => 'utf8'
]);
```

### Default drivers

By default the following drivers are available:

* mysql
* mongo
* rest

Driver names are case insentive.

### Adding and removing drivers

You can add drivers to the factory.

```php
$factory->set('xmldb', App\XmlDB::class);
```

If the driver name already exists it will be overwritten.

```php
$factory->set('mysql', App\MySqlConnection::class);
```

To remove a driver, set the class name to `null`.

```php
$factory->set('rest', null);
```


## Registry

A `ConnectionRegistry` holds a created database connections and gives them a name.

### Adding connections

```php
$connections = new Jasny\DB\Base\ConnectionRegistry();

$connections->set('default', new Jasny\DB\MySQL\Connection([]));
$connections->set('external', new Jasny\DB\MySQL\Rest(['host' => 'api.example.com']));
```

The same connection may be registered multiple times under different names.

### Getting connections

The `ConnectionsRegistry` interface extends the [`Interop\Container\ContainerInterface`][], defining an `get()` and
`has()` method.

The `get()` method will throw a `ConnectionNotFoundException` when trying to get an connection that doesn't exists.

```php
$db = $connections->get('default');

if ($connections->has('foo')) {
    // ...
}
```

_You are encouraged to type-hint against the `ContainerInterface`, rather than `ConnectionRegistry` if you're only
concerned with getting an connection._

### Configuration

Instead of manually creating and registring each connection, you can configure all connections using `configure()`.
This method takes an associated array (or `stdClass` object) of settings where each item is a new connection. The key
is used by the registry as name.

```php
$connections->configure([
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

To create database objects from configuration, the registry depends on a factory. By default it will create a new
`Base\ConnectionFactory`. If needed, you can explicitly set the factory:

```php
$factory = new Jasny\DB\Base\ConnectionFactory();
$factory->set('mysql', App\MySqlConnection::class);

$connections->setFactory($factory);
```


[`Interop\Container\ContainerInterface`]: https://github.com/container-interop/container-interop/blob/master/docs/ContainerInterface.md


