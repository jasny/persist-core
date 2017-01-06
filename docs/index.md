# Jasny DB

Jasny DB adds OOP design patterns to PHP's database extensions.

It is a [data access layer](https://en.wikipedia.org/wiki/Data_access_layer) (*not* a DB abstraction layer) for PHP.
It does allow you properly structure your model, while still using the methods and functionality of PHP's native
database extensions.

## Introduction

Jasny DB defines a number of components, each with it's own responsibility.

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

## Installation

This library is not intended to be installed directly. The Jasny DB library contains design pattern definitions and
implementations. It serves as an abstract base for concrete libraries implemented for specific PHP extensions.

## Implementations

* [Jasny\DB\MySQL][] extends [mysqli][]
* [Jasny\DB\Mongo][] extends the [MongoDB PHP library][]
* [Jasny\DB\REST][] extends [Guzzle][] for datasources implementing [REST][] and using JSON


[Connection]: connection.md
[Entity]: entity.md
[Entity set]: entity-set.md
[Data mapper]: data-mapper.md
[Filter]: filter.md

[Jasny\DB\MySQL]: https://github.com/jasny/db-mysql
[mysqli]: http://php.net/mysqli
[Jasny\DB\Mongo]: https://github.com/jasny/db-mongo
[MongoDB PHP library]: https://github.com/mongodb/mongo-php-library
[Jasny\DB\REST]: https://github.com/jasny/db-rest
[REST]: https://en.wikipedia.org/wiki/Representational_state_transfer
[Guzzle]: http://docs.guzzlephp.org/

