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
