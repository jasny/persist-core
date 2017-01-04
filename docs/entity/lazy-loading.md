Lazy loading
---

Jasny DB supports [lazy loading](http://en.wikipedia.org/wiki/Lazy_loading) of entities by allowing them to be created
as ghost. A ghost only hold a limited set of the entity's data, usually only the identifier. When other properties are
accessed it will load the rest of the data.

When a value is [casted](#type-casting) to an entity that supports lazy loading, a ghost of that entity is created.
