Connections
---

Connection objects are use used to interact with a database. Other object must use a connection object do actions like
getting getting, saving and deleting data from the DB.

### Registry
To register a connection use `Jasny\DB::connectionFactory()->register($name, $connection)`. To get a registered
connection, use `Jasny\DB::connectionFactory()->get($name)` or the shortcut `Jasny\DB::conn($name)`. Connections can be
removed from the registry using `Jasny\DB::connectionFactory()->unregister($name|$connection)`.

```php
$db = new Jasny\DB\MySQL\Connection();
Jasny\DB::connectionFactory()->register('foo');

Jasny\DB::conn('foo')->query();

Jasny\DB::connectionFactory()->unregister('foo');
```

The same connection may be registered multiple times under different names.

### Named connections
Connections implementing the `Namable` interface can register themselves to `Jasny\DB` using the `useAs($name)` method.
With the `getConnectionName()` you can get the name of a connection.

```php
$db = new Jasny\DB\MySQL\Connection();
$db->useAs('foo');

Jasny\DB::conn('foo')->query();
```

If you only have one DB connection name it 'default', since `$name` defaults to 'default'.

```php
$db = new Jasny\DB\MySQL\Connection();
$db->useAs('default');

Jasny\DB::conn()->query();
```

### Configuration

Instead of manually creating and configuring, you may configure connections using `Jasny\DB::configure()`. This static
property may hold the configuration for each connection. When using the `conn()` method, Jasny DB will automatically
create a new connection based on the configuration settings.

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

Jasny\DB::conn()->query();
Jasny\DB::conn('external')->get("/something");
```

`Jasny\DB::$drivers` holds a list of `Connection` classes with their driver name. The `createConnection($settings)`
method uses the `driver` setting to select the connection class. The other settings are passed to the connection's
constructor.
