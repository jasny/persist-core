# Jasny DB

Jasny DB adds OOP design patterns to PHP's database extensions.

* [Introduction](index.md)
<ul>
  * [Maintainable code](introduction/maintainable-code.md)
  * [Dependency injection](introduction/dependency-injection.md)
  * [Service locator](introduction/service-locator.md)
</ul>
* [Connection](connection.md)
* [Entity](entity.md)
<ul>
  * [Type casting](entity/type-casting.md)
  * [Identifiable](entity/identifiable.md)
  * [Change aware](entity/change-aware.md)
  * [Self aware](entity/self-aware.md)
  * [Dynamic](entity/dynamic.md)
  * [Enrichable](entity/enrichable.md)
  * [Redactable](entity/redactable.md)
  * [Validation](entity/validation.md)
  * [Lazy loading](entity/lazy-loading.md)
  * [Metadata](entity/metadata.md)
  * [Active record](entity/active-record.md)
</ul>
* [Data mapper](data-mapper.md)
<ul>
  * [Property mapping](data-mapper/property-mapping.md)
  * [Property casing](data-mapper/property-casing.md)
  * [Searching](data-mapper/searching.md)
  * [Sorting](data-mapper/sorting.md)
  * [Soft deletion](data-mapper/soft-deletion.md)
</ul>
* [Filter](filter.md)
* [Entity set](entity-set.md)

---

Jasny DB is a [data access layer](https://en.wikipedia.org/wiki/Data_access_layer) (*not* a DB abstraction layer) for
PHP. It does allow you properly structure your model, while still using the methods and functionality of PHP's native
database extensions.

## Installation

This library is not intended to be installed directly. The Jasny DB library contains design pattern definitions and
implementations. It serves as an abstract base for concrete libraries implemented for specific PHP extensions.

## Implementations

* [Jasny\DB\MySQL][] extends [mysqli][]
* [Jasny\DB\Mongo][] extends the [MongoDB PHP library][]
* [Jasny\DB\REST][] extends [Guzzle][] for datasources implementing [REST][]

[Jasny\DB\MySQL]: https://github.com/jasny/db-mysql
[mysqli]: http://php.net/mysqli
[Jasny\DB\Mongo]: https://github.com/jasny/db-mongo
[MongoDB PHP library]: https://github.com/mongodb/mongo-php-library
[Jasny\DB\REST]: https://github.com/jasny/db-rest
[REST]: https://en.wikipedia.org/wiki/Representational_state_transfer

---

[Next: Maintainable code »](introduction/maintainable-code.md)

