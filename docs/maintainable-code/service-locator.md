Service Locator
===

The `Jasny\DB` class is a [service locator][service locator pattern]. It grants access to [registries][registry pattern]
and [builders][builder pattern].

With the service locator your application can access any part of model from anywhere in the code, including other model
classes.

---

> **Beware!** Using a service locator will make it easier to create your application. However it can make the code more
> difficult to maintain (opposed to using [Dependency injection](#dependency-injection), because it becomes unclear when
> you would be introducing a breaking change.

## Mapper Registry

The service locator holds a single instance of a [mapper registry](mapper.md#registry). The `map()` method is a
shortcut for creating a mapper.

```php
class User implements Entity
{
    public function getTeams()
    {
        return Jasny\DB::map(Team::class)->fetchAll(['users' => $this]);
    }
}
```

If you need direct access to the mapper you can use

```php
$mappers = Jasny\DB::getMapperRegistry();
```

To use a custom mapper registry use

```php
$factory = new Jasny\DB\MapperFactory();
Jasny\DB::withMapperRegistry(new MyCustomMapperRegistry($factory));
```

## Connection Registry

The service locator holds a single instance of a [connection registry](connection.md#registry). If the application has
only one database connection, it can be simply registered as 'default'.

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

> **Note:** Using the database connection should be reserved to classes that have the specific function to interact with
>the databases, like a data mapper or data import class.


## EntitySet Builder

The service locator holds a single instance of a [entity set builder](entity-set.md#builder). The `entitySet()` method
will clone this instance, allow you to further specify the build.

```php
$users = Jasny\DB::entitySet()->forClass(User::class)->allowDuplicates()->create($records);
```


```php
$builder = new MyCustomEntitySetBuilder();
Jasny\DB::withEntitySetBuilder($builder);
```

## Testing

The service provider allows mocking the registries and builder using the `with...()` methods. However, you typically
shouldn't need to use this when testing your application.

The registries can be used to register mock objects. The entity set
builder has it's own [capability for testing](entity-set.md#testing).


[service locator pattern]: https://en.wikipedia.org/wiki/Service_locator_pattern
[registry pattern]: http://martinfowler.com/eaaCatalog/registry.html
[builder pattern]: https://en.wikipedia.org/wiki/Builder_pattern

---

[Next: Connection »](connection/index.md)

