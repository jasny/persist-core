![jasny-banner](https://user-images.githubusercontent.com/100821/62123924-4c501c80-b2c9-11e9-9677-2ebc21d9b713.png)

Persist - Database abstraction layer
========

[![Build Status](https://secure.travis-ci.org/jasny/persist.png?branch=master)](http://travis-ci.org/jasny/persist)
[![Scrutinizer Code Quality](https://scrutinizer-ci.com/g/jasny/persist/badges/quality-score.png?b=master)](https://scrutinizer-ci.com/g/jasny/persist/?branch=master)
[![Code Coverage](https://scrutinizer-ci.com/g/jasny/persist/badges/coverage.png?b=master)](https://scrutinizer-ci.com/g/jasny/persist/?branch=master)
[![Packagist Stable Version](https://img.shields.io/packagist/v/jasny/persist.svg)](https://packagist.org/packages/jasny/persist)
[![Packagist License](https://img.shields.io/packagist/l/jasny/persist.svg)](https://packagist.org/packages/jasny/persist)

Installation
---

This library should not be installed directly. The Persist library contains design pattern definitions and
implementations. It serves as an abstract base for concrete libraries implemented for specific PHP extensions.

### Implementations

* _Persist\SQL_ for MySQL, PostgreSQL, and SQLite using [PDO](https://http://php.net/pdo/) **TODO**
* [Persist\Mongo](http://github.com/jasny/persist-mongo) for [MongoDB](https://docs.mongodb.com/php-library/)
* _Persist\Dynamo_ for [DynamoDB](https://docs.aws.amazon.com/amazondynamodb/latest/developerguide/GettingStarted.PHP.html) 
  **TODO**
* _Persist\Elasticsearch_ for
    [Elasticsearch](https://www.elastic.co/guide/en/elasticsearch/client/php-api/current/) **TODO**

Usage
---

### Fetch a list

```php
use Persist\Option\Functions as opt;
use Persist\Mongo\Gateway\Gateway;

$collection = (new MongoDB\Client())->test->users;
$gateway = new Gateway($collection);

$list = $gateway
    ->fetch(
        opt\where([
            'invite.group' => 'the A-Team',
            'activation_date (min)' => new DateTime(),
            'role (any)' => ['user', 'admin', 'super']
        ]),
        opt\fields('name', 'role', 'age'),
        opt\limit(10),
    )
    ->map(function(array $user): string {
        return sprintf("%s (%s) - %s", $user['name'], $user['age'], $user['role']);
    });
```

### Read and write

```php
use Persist\Mongo\Gateway\Gateway;

$collection = (new MongoDB\Client())->test->users;
$gateway = new Gateway($collection);

$user = $gateway->fetch(opt\where(['id' => '12345']))->first();
$user->count = "bar";

$gateway->save($user);
```

### Update multiple

```php
use Persist\Option\Functions as opt;
use Persist\Update\Functions as update;
use Persist\Mongo\Gateway;

$collection = (new MongoDB\Client())->test->users;
$gateway = new Gateway($collection);

$gateway->update(
    opt\where(['type' => 'admin']),
    update\inc('reward', 100),
    update\set('rewarded', new DateTime()),
);
```

### Iterators and generators

Persist uses PHP iterators and generators. It's important to understand what they are and how they work. If you're
not familiar with this concept, first read
"[What are iterators?](https://github.com/improved-php-library/iterable#what-are-iterators)".

## Options

Database-specific options can be passed. Such options include limiting the number of results,
loading related data, sorting, etc. These options are passed to the query builder.

```php
$list = $gateway->fetch(
    opt\fields('name', 'role', 'age'),
    opt\where(['active' => true]),
    opt\sort('~activation_date', 'name'),
    opt\limit(10),
);
```

This library defines the concept of options, and a number of common options.

* `where(array $filter)`
* `fields(string ...$fields)`
* `omit(string ...$fields)`
* `sort(string ...$fields)`
* `limit(int limit, int offset = 0)`
* `page(int pageNr, int pageSize)` _(pagination is 1-indexed)_

For sorting, add a `~` in front of the field to sort in descending order.

Persist implementations may define additional options.

### Filter

The `where` option accepts a `$filter` argument. The filter is an associated array with a field key and
corresponding value.

A filter SHOULD always result in the same or a subset of the records you'd get when calling the method without a
filter.

```php
$zoo = $gateway->fetch(['reference' => 'zoo'])->first();
$count = $gateway->count(['bar' => 10]);
$gateway->update(['id' => 123], update\set(['type' => 'admin']));
```

Filter keys may include an operator. The following operator are supported by default

Key            | Value  | Description
-------------- | ------ | ---------------------------------------------------
"field"        | scalar | Field is the value
"field (not)"  | scalar | Field is not the value
"field (min)"  | scalar | Field is equal to or greater than the value
"field (max)"  | scalar | Field is equal to or less than the value
"field (any)"  | array  | Field is one of the values in the array
"field (none)" | array  | Field is none of the values in the array

To filter between two values, use both `(min)` and `(max)`.

If the field is an array, you may use the following operators

Key                | Value  | Description
------------------ | ------ | ---------------------------------------------------
"field (has)"      | scalar | The value is part of the field
"field (has-not)"  | scalar | The value is not part of the field
"field (has-any)"  | array  | Any of the values are part of the field
"field (has-all)"  | array  | All of the values are part of the field
"field (has-none)" | array  | None of the values are part of the field

For data stores that support structured data (as MongoDB) the field may use the dot notation to reference a deeper
properties.

_The filter is a simple associative array, rather than an array of objects, making it easier to pass (part of) the
HTTP query parameters as a filter._

## Gateway

A storage reader can be used to fetch from a persistent data storage (DB table, collection, etc). The storage is not
embedded to the service, but instead passed to it when calling a method.

Each implementation has its own Gateway service that converts the [generic filter](#filters) into a DB-specific query and
wraps the query result in an iterator pipeline.

The `fetch` and `count` methods accept a filter and options (`$opts`). The available options differ per implementation.
Typically you can set the fields that should be part of the result, a limit and offset and possibly which metadata you
want to grab.

The `save`, `update` and `delete` methods accept options (`$opts`).

### fetch

    Result fetch(OptionInterface ...$opts)

Query and fetch data.

### count

    int count(OptionInterface ...$opts)
    
Query and count result.

### save

    array|object save(array|object $item, OptionInterface ...$opts)

Save the item. If an item has a unique id update it, otherwise add it.

The method will return the item, possibly modified with generated values, like auto-increment id.

### saveAll

    Result saveAll(iterable $items, OptionInterface ...$opts)

Save the items. If an item has a unique id update it, otherwise add it.

The method returns a result with the items, possibly modified with generated values, like auto-increment ids.

### update

    Result update(UpdateInstruction|UpdateInstruction|OptionInterface ...$opts)
    
Query and update records.

```php
use Persist\Update as update;
use Persist\Mongo\Gateway as Gateway;

$userCollection = (MongoDB\Client())->tests->users;
$gateway = Gateway::basic()->forCollection($userCollection);

$gateway->update(opt\where(['id' => 10]), update\set('last_login', new DateTime()), update\inc('logins'));
```

The `$changes` argument must be one or more `UpdateOperation` objects. Rather than creating such an object by hand, the
following helper functions exist in the `Persist\Update\Functions` namespace:

* `set(iterable $values)`
* `set(string $field, mixed $value)`
* `patch(string $field, array|object $value)`
* `inc(string $field, int|float $value = 1)`
* `dec(string $field, int|float $value = 1)`
* `mul(string $field, int|float $value)`
* `div(string $field, int|float $value)`

If the field is an array, the following operations are also available
* `push(string $field, mixed $value, ...)` - Add elements to the array
* `pull(string $field, mixed $value, ...)` - Remove elements from the array

To prevent accidentally swapping the changes and filter, passing a normal associative array is not allowed. Instead use
`update\set($values)`, where values are all values that need to be set.

If you want to update every record of the storage (table, collection, etc) you have to supply an empty array as filter.

The method returns a result without any items, but it may contain metadata.

### delete

    Result delete(OptionInterface ...$opts)
    
Query and delete records.

The method returns a result without any items, but it may contain metadata.

## Result

The `Gateway::fetch()` method returns a `Result` object which extends
[iterator pipeline](https://github.com/improved-php-library/iteratable). As such, it provides methods, like map/reduce,
to further process the result.

```php
$employees = $gateway->fetch(opt\where(['type' => 'admin']))
    ->map(fn(array $user) => $user + ['fullname' => $user['firstname'] . ' ' . $user['lastname']])
    ->group(fn(array $user) => $user['organization_id']);
```

### Metadata

Services may add metadata to the result, this may include the total number of results, if the result set is limited, or
the indexes that were used for the query.

The metadata is available through the `getMeta()` method.

```php
$meta = $result->getMeta(); // ['total' => 42, 'cursor_id' => "94810124093"] 
$totalCount = $result->getMeta('total'); // 42
```

Rather than getting all meta, you can get a specific item by specifying the key.

### Custom result

The read service has a `withResultBuilder()` which takes a `ResultBuilder` object. You can add steps to this which
are performed for every result.

```php
use Persist\Result\Result;
use Persist\Mongo\Gateway\Gateway;

$resultBuilder = Result::build()
    ->filter(fn ($value) => $value !== null);

$gateway = (new Gateway())->withResultBuilder($resultBuilder);
```

If needed, you can extend the `Result` class to add custom methods

```php
use Persist\Result\Result;

class MyResult extends Result
{
    public function product(iterable $iterable)
    {
        $product = 1;
        
        foreach ($iterable as $value) {
            $product *= $value;
        }
        
        return $product;
    }
}
```

Use `build()` on your custom class to get a result builder that creates your custom result object.

```php
use Persist\Mongo\Gateway\Gateway;

$gateway = (new Gateway())->withResultBuilder(MyResult::build());
```

## Field map

The field map can be used both as a step in a 'prepare' stage of a query or as step of the result builder. If convert
database field names into field names used in the PHP app and visa versa.

Construct the map using an associative array in the form `[from => to]`. The `flip()` method flips the `from` and `to`.

```php
use Persist\Map\DeepMap;

$fieldMap = new DeepMap(['ref' => 'reference', 'foo_bar_setting' => 'foo_bar']);

$gateway = new Gateway();
$queryBuilder = $gateway->getQueryBuilder()->onPrepare($fieldMap);
$resultBuilder = $gateway->getResultBuilder()->then($fieldMap->flip());

$gateway = $gateway
    ->withQueryBuilder($queryBuilder)
    ->withResultBuilder($resultBuilder);
```
