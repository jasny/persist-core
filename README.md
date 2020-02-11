![jasny-banner](https://user-images.githubusercontent.com/100821/62123924-4c501c80-b2c9-11e9-9677-2ebc21d9b713.png)

Jasny DB
========

[![Build Status](https://secure.travis-ci.org/jasny/db.png?branch=master)](http://travis-ci.org/jasny/db)
[![Scrutinizer Code Quality](https://scrutinizer-ci.com/g/jasny/db/badges/quality-score.png?b=master)](https://scrutinizer-ci.com/g/jasny/db/?branch=master)
[![Code Coverage](https://scrutinizer-ci.com/g/jasny/db/badges/coverage.png?b=master)](https://scrutinizer-ci.com/g/jasny/db/?branch=master)
[![Packagist Stable Version](https://img.shields.io/packagist/v/jasny/db.svg)](https://packagist.org/packages/jasny/db)
[![Packagist License](https://img.shields.io/packagist/l/jasny/db.svg)](https://packagist.org/packages/jasny/db)

Installation
---

This library is not intended to be installed directly. The Jasny DB library contains design pattern definitions and
implementations. It serves as an abstract base for concrete libraries implemented for specific PHP extensions.

### Implementations

* _Jasny\DB\PDO_ for [PDO](https://http://php.net/pdo/) **TODO**
* [Jasny\DB\Mongo](http://github.com/jasny/db-mongo) for [MongoDB](https://docs.mongodb.com/php-library/)
* _Jasny\DB\Dynamo_ for [DynamoDB](https://docs.aws.amazon.com/amazondynamodb/latest/developerguide/GettingStarted.PHP.html) 
  **TODO**
* _Jasny\DB\Elasticsearch_ for
    [Elasticsearch](https://www.elastic.co/guide/en/elasticsearch/client/php-api/current/) **TODO**
* _Jasny\DB\REST_ for data sources implementing
    [REST](http://en.wikipedia.org/wiki/Representational_state_transfer) **TODO**

Usage
---

### Fetch a list

```php
use Jasny\DB\Option as opts;
use Jasny\DB\Mongo\Reader;

$collection = (new MongoDB\Client)->test->users;
$reader = new Reader($collection);

$list = $reader
    ->fetch(
        [
            'invite.group' => 'the A-Team',
            'activation_date (min)' => new DateTime(),
            'role (any)' => ['user', 'admin', 'super']
        ],
        [
            opts\fields('name', 'role', 'age'),
            opts\limit(10)
        ]
    )
    ->map(function(array $user): string {
        return sprintf("%s (%s) - %s", $user['name'], $user['age'], $user['role']);
    });
```

### Read and write

```php
use Jasny\DB\Mongo\Reader;
use Jasny\DB\Mongo\Writer;

$collection = (new MongoDB\Client)->test->users;
$reader = new Reader($collection);
$writer = new Writer($collection);

$user = $reader->fetch(['id' => '12345'])->first();
$user->count = "bar";

$writer->save([$user]);
```

### Update multiple

```php
use Jasny\DB\Update as update;
use Jasny\DB\Mongo\Writer;

$collection = (new MongoDB\Client)->test->users;
$writer = new Writer($collection);

$writer->update(
    ['type' => 'admin'],
    [update\inc('reward', 100), update\set('rewarded', new DateTime())
]);
```

### Iterators and generators

Jasny DB uses PHP iterators and generators. It's important to understand what they are and how they work. If you're
not familiar with this concept, first read
"[What are iterators?](https://github.com/improved-php-library/iterable#what-are-iterators)".

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
HTTP query parameters as filter._

## Options

In additions to a filter, database specific options can be passed. Such options include limiting the number of results,
loading related data, sorting, etc. These options are passed to the query builder.

```php
use Jasny\DB\Option as opts;
use Jasny\DB\Mongo\Reader;

$users = (new MongoDB\Client)->test->users;
$reader = new Reader;

$list = $reader
    ->fetch(
        $users,
        ['active' => true],
        [
            opts\fields('name', 'role', 'age'),
            opts\limit(10),
            opts\sort('~activation_date', 'name')
        ]
    );
```

This library defines the concept of options and a number of common options.

* `fields(string ...$fields)`
* `omit(string ...$fields)`
* `sort(string ...$fields)`
* `limit(int limit, int offset = 0)`
* `page(int pageNr, int pageSize)` _(pagination is 1-indexed)_

For sorting, add a `~` in front of the field to sort in descending order.

Jasny DB implementations may define additional options.

## Read service

A storage reader can be used to fetch from a persistent data storage (DB table, collection, etc). The storage is not
embedded to the service, but instead passed to it when calling a method.

Each implementation has its own Reader service that converts the [generic filter](#filters) into a DB specific query and
wraps the query result in an iterator pipeline.

The `fetch` and `count` methods accept a filter and options (`$opts`). The available options differ per implementation.
Typically you can set the fields that should be part of the result, a limit and offset and possibly which metadata you
want to grab.

### fetch

    Result fetch($storage, array $filter, array $opts = [])

Query and fetch data.

### count

    int count($storage, array $filter, array $opts = [])
    
Query and count result.

### Result

The `Read::fetch()` method returns a `Result` object which extends
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
use Jasny\DB\Mongo\Reader;

$reader = new Reader();
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
use Jasny\DB\Mongo\Reader;

$resultBuilder = (new PipelineBuilder)
    ->then(function(iterable $iterable) {
        return MyResult($iterable);
    });

$reader = (new Reader)->withResultBuilder($resultBuilder);
```

## Write service

A storage writer service is used to save, update and delete data of the persistent storage. Similar to the read service,
the storage needs to be passed to each method.

Each implementation has its own Writer service that converts the [generic filter](#filters) into a DB specific query.

The `save`, `update` and `delete` methods accept options (`$opts`).

### save

    iterable save($storage, iterable $items, array $opts = [])

Save the items. If an item has a unique id update it, otherwise add it.

Multiple items must be specified. If you only want to save one item, wrap it in an array as `save($storage, [$item])`.

The method returns an array or other iterable with generated properties per entry, like auto-increment ids. Even if
`$items` is an array of objects, the generated properties will not automatically be set for these objects.

### update

    void update($storage, array $filter, UpdateInstruction|UpdateInstruction[] $changes, array $opts = [])
    
Query and update records.

```php
use Jasny\DB\Update as update;
use Jasny\DB\Mongo\Writer as Writer;

$userCollection = (MongoDB\Client())->tests->users;
$writer = Writer::basic()->forCollection($userCollection);

$writer->update(['id' => 10], [update\set('last_login', new DateTime()), update\inc('logins')]);
```

The `$changes` argument must be one or more `UpdateOperation` objects. Rather than creating such an object by hand, the
following helper functions exist in the `Jasny\DB\Update` namespace:

* `set(iterable $values)`
* `set(string $field, $value)`
* `patch(string $field, array|object $value)`
* `inc(string $field, int|float $value = 1)`
* `dec(string $field, int|float $value = 1)`
* `mul(string $field, int|float $value)`
* `div(string $field, int|float $value)`

If the field is an array, the following operations are also available
* `push(string $field, $value, ...)` - Add elements to the array
* `pull(string $field, $value, ...)` - Remove elements from the array

To prevent accidentally swapping the changes and filter, passing a normal associative array is not allowed. Instead use
`update\set($values)`, where values are all values that need to be set.

If you want to update every record of the storage (table, collection, etc) you have to supply an empty array as filter.

### delete

    void delete($storage, array $filter, array $opts = [])
    
Query and delete records.

## Field map

The field map can be used both as a step in a 'prepare' stage of a query or as step of the result builder. If convert
database field names into field names used in the PHP app and visa versa.

Construct the map using an associative array in the form `[from => to]`. The `flip()` method flips the `from` and `to`.

```php
use Jasny\DB\Map\DeepMap;

$fieldMap = new DeepMap(['ref' => 'reference', 'foo_bar_setting' => 'foo_bar']);

$reader = new Reader();
$queryBuilder = $reader->getQueryBuilder()->onPrepare($fieldMap);
$resultBuilder = $reader->getResultBuilder()->then($fieldMap->flip());

$reader = $reader
    ->withQueryBuilder($queryBuilder)
    ->withResultBuilder($resultBuilder);
```

## Custom filters

The functionality of the basic filters is limited. With the query builder, custom filters may be added. These filter
do not have to correspond to one field, but can closely match the business logic.

```php
use Jasny\DB\QueryBuilder\StagedQueryBuilder;
use Jasny\DB\QueryBuilder\Prepare\CustomFilter;
use Jasny\DB\Mongo\Reader;
use Jasny\DB\Mongo\QueryBuilder\Query;

$clients = (new MongoDB\Client)->test->clients; 
$reader = new Reader();

$queryBuilder = (new StagedQueryBuilder)
    ->onCompose(new CustomFilter('prominent', function(Query $query, string $field, string $operator, $value) {
        $condition = ($operator === 'not' xor !$value)
            ? ['$or' => ['sold' => ['$lt' => 1000], 'activation_date' => ['$gt' => new DateTime('-1 year')]]]
            : ['$and' => ['sold' => ['$gte' => 1000], 'activation_date' => ['$lte' => new DateTime('-1 year')]]]
        
        $quey->add($condition);
    }));

$clientReader = $reader->withQueryBuilder($queryBuilder);

$prominentResellers = $clientReader->fetch($clients, ['prominent' => true, 'reseller' => true]);
```

Both Reader and Search service support the `withQueryBuilder()` method, which creates a new copy of the service.

Custom filters are DB specific as the accumulator (first argument) is DB specific.

## Custom query builder

It's possible to create a custom query builder from scratch. You class needs to implement the `QueryBuilding`.

```php
$reader = (new Reader)->withQueryBuilder(new MyQueryBuilder());
```

### Staged query builder

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

    void callback($accumulator, $field, $operator, $value, array $opts) 

The accumulator is typically an object. The exact type differs per implementation.

Other steps in this stage may replace these functions with custom logic, like with the `CustomFilter` class.

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

#### Custom update query builder

The writer service can be used to update multiple records at once. It creates an update query using not one but two
query builders.

The **update** query builder takes the update operations, which creates the change set (`SET field = value` in an SQL
query). It can be replaced using `$writer->withUpdateQueryBuilder(...)`.

The first step in the prepare stage converts update instructions into an iterable with the field name and operator as
key and value as value. The output this step is similar to that of the filter parser. From there the builder works the
same as explained in the previous section.

The **filter** query builder, which is also used by the other methods to build the conditions (`WHERE ...
` part of an SQL query).

#### Custom save query builder

For saving the writer also uses the staged query builder, but this time it functions a bit different, as we start with
a set of items instead of field, operator and value. It may be replaced using `$writer->withSaveQueryBuilder(...)`.

##### 1. prepare
The prepare step does nothing by default, but can be used to apply field mapping and casting. Do note that every item of
the iterator is an array with `[field => value]` pairs. So you'd need to traverse through those, typically with
`iterable_map`.

```php
use Improved as i;
use Jasny\DB\Map\DeepMap;

$fieldMap = new DeepMap(['ref' => 'reference', 'foo_bar_setting' => 'foo_bar']);

$writer = new Writer();
$queryBuilder = $writer->getSaveQueryBuilder()->onPrepare(function(iterable $items) use ($fieldMap) {
    return i\iterable_map($items, $fieldMap);
});

$writer = $writer->withSaveQueryBuilder($queryBuilder);
``` 

##### 2. compose
The compose stage first groups items together. It might create batches of (max) 100 items or group all existing and new
together. It depends on to which extends saving these items can be combined into a single query.

Next it will set these grouped values as key and change the value of the iterator into a callback function

    mixed callback(array $items, array $opts) 

Additional steps could replace these functions, but as there is no clear way to identify them, this is typically not a
good idea. In other words; you typically don't want to add steps to the compose stage of an insert query builder.

##### 3. build
The build still calls all the callbacks created in the compose step. However, there is no accumulator, as the items
are already grouped. Each callback function must return the terms of a query.

##### 4. finalize
The finalize will iterate over the query terms and turn them into things the underlying storage driver understands.
Where the other query builders output a single query, the save query builder returns an iterable with one or more
such queries.
