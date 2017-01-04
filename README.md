Jasny DB
========

[![Build Status](https://secure.travis-ci.org/jasny/db.png?branch=master)](http://travis-ci.org/jasny/db)
[![Code Coverage](https://scrutinizer-ci.com/g/jasny/db/badges/coverage.png?b=master)](https://scrutinizer-ci.com/g/jasny/db/?branch=master)

Jasny DB adds OOP design patterns to PHP's database extensions. It does this as a
[data access layer](https://en.wikipedia.org/wiki/Data_access_layer) (*not* a DB abstraction layer). This allows you
properly structure your model, while still using the methods and functionality of PHP's native database extensions.

## Installation
This library is not intended to be installed directly. The Jasny DB library contains design pattern definitions and
implementations. It serves as an abstract base for concrete libraries implemented for specific PHP extensions.

## Implementations

* [Jasny\DB\MySQL](http://github.com/jasny/db-mysql) extends [mysqli](http://php.net/mysqli)
* [Jasny\DB\Mongo](http://github.com/jasny/db-mongo) extends [mongo](http://php.net/mongo)
* [Jasny\DB\REST](http://github.com/jasny/db-rest) for datasources implementing
  [REST](http://en.wikipedia.org/wiki/Representational_state_transfer)

## Documentation

This package is fully documented [here](http://jasny-db.readthedocs.io/en/latest/).
