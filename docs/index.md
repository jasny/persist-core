# Jasny DB

Jasny DB adds OOP design patterns to PHP's database extensions.

* [Home](index.md)
* [Maintainable code](maintainable-code/index.md)
<ul>
  * [Dependency injection](maintainable-code/dependency-injection.md)
  * [Service locator](maintainable-code/service-locator.md)
</ul>
* [Connection](connection/index.md)
* [Entity](entity/index.md)
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
* [Entity set](entity-set/index.md)
* [Data mapper](data-mapper/index.md)
<ul>
  * [Property mapping](data-mapper/property-mapping.md)
  * [Property casing](data-mapper/property-casing.md)
  * [Searching](data-mapper/searching.md)
  * [Sorting](data-mapper/sorting.md)
  * [Soft deletion](data-mapper/soft-deletion.md)
</ul>
* [Filter](filter/index.md)

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

## Introduction

A [Connection][] can be used to used to load and store data from the database. Jasny DB doesn't abstract a connection
object, so the exact use differs per implementation.

An [Entity][] is a representation of "thing" in your application, like a user, product, article, etc. A data
representation of the entity is stored in the database, so the entity can be loaded. An entity SHOULD only be
concerned with business logic and SHOULD NOT hold any database specific logic.

An [Entity set][] is a collection of entities, similar to an array entities. The only difference is that has methods to
interact with all entities in the set at once, rather than needed to loop through them.

A [Data mapper][] is the bridge between the connection object and the entity. It's responsible for loading data from
the database and creating an entity. It's also responsible for turning an entity into data, so it can be stored in the
database.

A [Filter][] is used by the data mapper to turn filter conditions into database logic, like an SQL query.


[Jasny\DB\MySQL]: https://github.com/jasny/db-mysql
[mysqli]: http://php.net/mysqli
[Jasny\DB\Mongo]: https://github.com/jasny/db-mongo
[MongoDB PHP library]: https://github.com/mongodb/mongo-php-library
[Jasny\DB\REST]: https://github.com/jasny/db-rest
[REST]: https://en.wikipedia.org/wiki/Representational_state_transfer

[Connection]: connection/index.md
[Entity]: entity/index.md
[Data mapper]: data-mapper/index.md
[Filter]: filter/index.md

---

[Next: Maintainable code »](introduction/maintainable-code.md)

