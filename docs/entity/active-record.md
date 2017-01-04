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
