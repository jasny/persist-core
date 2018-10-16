Jasny DB
========

[![Build Status](https://secure.travis-ci.org/jasny/db.png?branch=master)](http://travis-ci.org/jasny/db)
[![Scrutinizer Code Quality](https://scrutinizer-ci.com/g/jasny/db/badges/quality-score.png?b=master)](https://scrutinizer-ci.com/g/jasny/db/?branch=master)
[![Code Coverage](https://scrutinizer-ci.com/g/jasny/db/badges/coverage.png?b=master)](https://scrutinizer-ci.com/g/jasny/db/?branch=master)
[![Packagist Stable Version](https://img.shields.io/packagist/v/jasny/db.svg)](https://packagist.org/packages/jasny/db)
[![Packagist License](https://img.shields.io/packagist/l/jasny/db.svg)](https://packagist.org/packages/jasny/db)

Database abstraction layer.

_All objects are immutable._

Installation
---

This library is not intended to be installed directly. The Jasny DB library contains design pattern definitions and
implementations. It serves as an abstract base for concrete libraries implemented for specific PHP extensions.

### Implementations

* [Jasny\DB\PDO](http://github.com/jasny/db-pdo) for [PDO](https://http://php.net/pdo/)
* [Jasny\DB\Mongo](http://github.com/jasny/db-mongo) for [MongoDB](https://docs.mongodb.com/php-library/)
* [Jasny\DB\Dynamo](http://github.com/jasny/db-dynamo) for
    [DynamoDB](https://docs.aws.amazon.com/amazondynamodb/latest/developerguide/GettingStarted.PHP.html)
* [Jasny\DB\Elasticsearch](http://github.com/jasny/db-elasticsearch) for
    [Elasticsearch](https://www.elastic.co/guide/en/elasticsearch/client/php-api/current/)
* [Jasny\DB\REST](http://github.com/jasny/db-rest) for data sources implementing
    [REST](http://en.wikipedia.org/wiki/Representational_state_transfer)

Usage
---

#### Fetch a list

```php
use Jasny\DB\Mongo\Read\MongoReader;

$users = (new MongoDB\Client)->test->users;
$reader = new MongoReader;

$list = $reader
    ->fetch(
        $users,
        [
            'invite.group' => 'A team',
            'activation_date (min)' => new DateTime(),
            'role (any)' => ['user', 'admin', 'super']
        ],
        [
            'fields' => ['name', 'role', 'age'],
            'limit' => 10
        ]
    )
    ->map(function(array $user): string {
        return sprintf("%s (%s) - %s", $user['name'], $user['age'], $user['role']);
    })
    ->toArray();
```

#### Read and write

```php
use Jasny\DB\Mongo\Read\MongoReader;
use Jasny\DB\Mongo\Write\MongoWriter;

$users = (new MongoDB\Client)->test->users;
$reader = new MongoReader;
$writer = new MongoWriter;

$user = $reader->fetch($users, ['id' => '12345'])->first();
$user->count = "bar";

$writer->save($users, [$user]);
```

#### Update multiple

```php
use Jasny\DB\Mongo\Write\MongoWriter;

$users = (new MongoDB\Client)->test->users;
$writer = new MongoWriter;

$writer->update($users, (object)['access' => 1000], ['type' => 'admin']);
```

_**Jasny DB makes extensive use of iterators and generators.** It's important to understand what they are and how they
work. If you're not familiar with this concept, first read
"[What are iterators?](https://github.com/improved-php-library/iterable#what-are-iterators)"._

## Filters

The reader and writer methods accept a `$filter` argument. The filter is an associated array with field key and
corresponding value.

A filter SHOULD always result in the same or a subset of the records you'd get when calling the method without a
filter.

```php
$zoo = $reader->fetch($storage, ['reference' => 'zoo'])->first();
$count = $reader->count($storage, ['bar' => 10]);
$writer->update($storage, (object)['access' => 1000], ['type' => 'admin']);
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

If the field is an array, you may use the following operators

Key            | Value  | Description
-------------- | ------ | ---------------------------------------------------
"field"        | scalar | The value is part of the field
"field (not)"  | scalar | The value is not part of the field
"field (any)"  | array  | Any of the values are part of the field
"field (all)"  | array  | All of the values are part of the field
"field (none)" | array  | None of the values are part of the field

To filter between two values, use both `(min)` and `(max)`.

#### Custom filters

The functionality of the basic filters is limited. With the query builder, custom filters may be added. These filter
do not have to correspond to one field, but can closely match the business logic.

```php
use Jasny\DB\QueryBuilder\StagedQueryBuilder;
use Jasny\DB\Mongo\Read\MongoReader;
use Jasny\DB\Mongo\QueryBuilder\Query;

$reader = new MongoReader();

$queryBuilder = (new StagedQueryBuilder)
    ->withFilter('prominent', function(Query $query, $field, $operator, $value) {
        $condition = ($operator === 'not' xor !$value)
            ? ['$and' => ['totalSale' => ['$gte' => 1000], 'activation_date' => ['$lte' => new DateTime('-1 year')]]]
            : ['$or' => ['totalSale' => ['$lt' => 1000], 'activation_date' => ['$gt' => new DateTime('-1 year')]]];
        
        $quey->add($condition);
    });

$clientReader = $reader->withQueryBuilder($queryBuilder);

$prominentResellers = $clientReader->fetch(['prominent' => true, 'reseller' => true]);
```

Both Reader and Search service support the `withQueryBuilder()` method, which creates a new copy of the service.

Custom filters are DB specific as the accumulator (first argument) is DB specific.

#### Custom query builder

It's possible to create a custom query builder from scratch. You class needs to implement the `QueryBuilderInterface`

```php
$reader = (new MongoReader)->withQueryBuilder(new MyQueryBuilder());
```

Alternatively, it's possible to customize a staged query builder. (Which also needs to be set, as query builders are
immutable objects.) The staged builder has 4 stages

##### 1. prepare
The first step in the first stage parses the filter keys, so the iterator key is
`["field" => string, "operator" => string]`.

Subsequently the values may be cast into values accepted by the db driver. A field map might be applied for aliased
fields. Etc.

```php
$newBuilder = $builder->onPrepare(function(iterable $iterator, array $opts) {
    foreach ($iterator as $info => $value) {
        $info['field'] = i\string_case_convert($info['field'], i\STRING_LOWERCASE);
        $value = ($value !== '' ? $value : null);
        
        yield $info => $value;
    }
});
```

##### 2. compose
The compose stage starts with a step that creates a callback function. For each entry, the value is added to the info
which is present as key, which now is `["field" => string, "operator" => string, "value" => mixed]`. The value is this
callback function with signature;

    void callback($accumulator, $field, $operator, $value, $opts) 

The accumulator is typically an object. The exact type differs per implementation.

Other steps in this stage may replace these functions with custom logic. The `withFilter()` method adds steps in the
compose stage.

```php
$newBuilder = $builder->onCompose(function(iterable $iterator, array $opts) {
    // ...
});
```
    
##### 3. build
The build stage creates the accumulator and calls each of the functions created in the compose stage.

Additional steps may add logic to the accumulator object.

```php
$newBuilder = $builder->onBuild(function(Query $query, array $opts) {
    if (isset($opts['page'])) {
        $query = $query->withLimit($opts['page'] * 10);
    }
    
    return $query;
});
```

##### 4. finalize
The last stage takes the accumulator and turns it into something that the underlying storage driver understands. This
can be an array, an SQL query as string or even an HTTP request.

Additional steps can customize this final result.

```php
$newBuilder = $builder->onFinalize(function(string $query, array $opts) {
    // ...
});
```

### Field map

_TODO: Add text here_

## Read service

A storage reader can be used to fetch from a persistent data storage (DB table, collection, etc). The storage is not
embedded to the service, but instead passed to it when calling a method.

Each implementation has its own Reader service that converts the [generic filter](#filters) into a DB specific query and
wraps the query result in an iterator pipeline.

The `fetch` and `count` methods accept a filter and options (`$opts`). The available options differ per implementation.
Typically you can set the fields that should be part of the result, a limit and offset and possibly which metadata you
want to grab.

#### fetch

    Result fetch($storage, array $filter, array $opts = [])

Query and fetch data.

#### count

    int count($storage, array $filter, array $opts = [])
    
Query and count result.

### Result

The `Reader->fetch()` method returns a `Result` object which extends
[iterator pipeline](https://github.com/improved-php-library/iteratable). As such, it provides methods, like map/reduce,
to further process the result.

Services may add metadata to the result, this may include the total number of results, if the resultset is limited or
the indexes that were used for the query.

The metadata is available through the `getMeta()` method.

```php
$meta = $result->getMeta(); // ['total' => 42, 'cursor_id' => "94810124093"] 
$totalCount = i\type_check($result->getMeta('total'), 'int'); // 42
```

Rather than getting all meta, you can get a specific item by specifying the key.

#### Custom result

The read service has a `withResultBuilder()` which takes a `PipelineBuilder` object. You can add steps to this which
are performed for every result.

```php
use Jasny\DB\Mongo\Read\MongoReader;

$reader = new MongoReader();
$resultBuilder = $reader->getResultBuilder()
    ->filter(function($value) {
        return $value !== null;
    });

$reader = $reader->withResultBuilder($resultBuilder);
```

If needed, you can extend to `Read\Result` class to add custom methods

```php
use Jasny\DB\Mongo\Read\Result;

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

The builder must return a `Result` object. Use the `then()` method of your builder to turn an ordinary `Pipeline` into a
`Result`.

```php
use Improved\IteratorPipeline\PipelineBuilder;
use Jasny\DB\Mongo\Read\MongoReader;

$resultBuilder = (new PipelineBuilder)
    ->then(function(iterable $iterable) {
        return MyResult($iterable);
    });

$reader = (new MongoReader)->withResultBuilder($resultBuilder);
```

## Write service

A storage writer service is used to save, update and delete data of the persistent storage. Similar to the read service,
the storage needs to be passed to each method.

Each implementation has its own Writer service that converts the [generic filter](#filters) into a DB specific query.

The `save`, `update` and `delete` methods accept options (`$opts`). The available options differ per implementation.

### save

    iterable save($storage, iterable $items, array $opts = [])

Save the data.

Multiple items must be specified. If you only want to save one item, wrap it in an array as `save($storage, [$item])`.

The method returns an array or other iterable with generated properties per entry, like auto-increment ids.

#### Custom save query builder

_TODO: How does this work?_

### update

    void update($storage, \stdClass $changes, array $filter, array $opts = [])
    
Query and update records.

The `$changes` must be an `stdClass` object rather than an array. This is primarily done to prevent a small mistake of
switching `$changes` and `$filter` to ruin your whole database.

If you want to update every record of the storage (table, collection, etc) you have to supply an empty array as filter.

#### Custom update query builder

_TODO: How to process the changes so they end up in the query?_

### delete

    void delete($storage, array $filter, array $opts = [])
    
Query and delete records.

If you want to delete every record of the storage (table, collection, etc) you have to supply an empty array as filter.
