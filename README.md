Jasny DB
========

[![Build Status](https://secure.travis-ci.org/jasny/db.png?branch=master)](http://travis-ci.org/jasny/db)
[![Scrutinizer Code Quality](https://scrutinizer-ci.com/g/jasny/db/badges/quality-score.png?b=master)](https://scrutinizer-ci.com/g/jasny/db/?branch=master)
[![Code Coverage](https://scrutinizer-ci.com/g/jasny/db/badges/coverage.png?b=master)](https://scrutinizer-ci.com/g/jasny/db/?branch=master)
[![Packagist Stable Version](https://img.shields.io/packagist/v/jasny/db.svg)](https://packagist.org/packages/jasny/db)
[![Packagist License](https://img.shields.io/packagist/l/jasny/db.svg)](https://packagist.org/packages/jasny/db)

Database abstraction layer for PHP.

## Installation
This library is not intended to be installed directly. The Jasny DB library contains design pattern definitions and
implementations. It serves as an abstract base for concrete libraries implemented for specific PHP extensions.

## Implementations

* [Jasny\DB\MySQL](http://github.com/jasny/db-mysql) extends [mysqli](http://php.net/mysqli)
* [Jasny\DB\Mongo](http://github.com/jasny/db-mongo) extends [mongo](http://php.net/mongo)
* [Jasny\DB\REST](http://github.com/jasny/db-rest) for data sources implementing
  [REST](http://en.wikipedia.org/wiki/Representational_state_transfer)

## Documentation

### CRUD

The CRUD service is 
