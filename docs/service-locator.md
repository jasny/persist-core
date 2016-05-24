Service Locator
---

The `Jasny\DB` class is a [service locator][service locator pattern]. It grant access to [registries][registry pattern]
and [builders][builder pattern].


## Methods

#### [[`Connection`](connections.md) conn(string $name = 'default')]
Get a connection from the registry.

_This is a shortcut of `Jasny\DB::getConnectionRegistry()->get($name)`._

**Returns a **


#### [`map(string $className)`]
Get a data mapper for an entity class.

_This is a shortcut of `Jasny\DB::getMapperRegistry()->get($name)`._

**Returns a [Mapper](mapper.md)**


#### [`entitySet()`]
Get the builder to create an EntitySet.

**Returns an [EntitySetBuilder](/entity-set.md#builder)**

```php
$entitySet = Jasny\DB::entitySet()->forClass(User::class)->create($data);
```

#### [`getConnectionRegistry()`]
Get the registry with DB connections.

**Returns a [ConnectionRegistry](connections.md#registry)**


#### [`withConnectionRegistry()`]
Get the registry with DB connections.

**Returns a [ConnectionRegistry](connections.md#registry)**


#### [`Jasny\DB\EntitySetFactory getMapperFactory()`]
Get the factory for data mappers.

**Returns a [MapperFactory](mappers.md#factory)**

#### [`getMapperRegistry()`]
Get the registry for with data mappers.

**Returns a [MapperRegistry](mappers.md#registry)**

#### [`getMapperFactory()`]
Get the registry for data mappers.

**Returns a [MapperFactory](mappers.md#factory)**


## Testing

```
Jasny\DB::entitySet()->forClass(User::class)->does(function($entities, $flags) use ($entitySetMock) {
    return $entitySetMock();
});
```



[service locator pattern]: https://en.wikipedia.org/wiki/Service_locator_pattern
[registry pattern]: http://martinfowler.com/eaaCatalog/registry.html
[builder pattern]: https://en.wikipedia.org/wiki/Builder_pattern
