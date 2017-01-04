Type casting
---

Entities support type casting. This is done based on the metadata. Type casting is implemented by the
[Jasny\Meta](http://www.github.com/jasny/meta) library.

### Internal types
For [php internal types](http://php.net/types) normal [type juggling](http://php.net/type-juggling) is used. Values
aren't blindly casted. For instance casting `"foo"` to an integer would trigger a warning and skip the casting.

### Objects
Casting a value to an `Identifiable` entity that supports [Lazy Loading](#lazy-loading), creates a ghost object.
Entities that implement `ActiveRecord` or have a `DataMapper`, but do not support `LazyLoading` are fetched from the
database.

Casting a value to a non-identifiable entity will call the `Entity::fromData()` method.

Casting to any other type of object will create a new object normally. For instance casting "bar" to `Foo` would result
in `new Foo("bar")`.
