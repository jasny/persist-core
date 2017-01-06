Filter
---

Fetch methods accept a `$filter` argument. The filter is an associated array with field name and corresponding
value. _Note that the `fetch()` methods takes either a unique ID or filter._

A filter SHOULD always return the same or less results that calling the method without a filter. 

```php
$foo   = Foo::fetch(['reference' => 'zoo']);
$foos  = Foo::fetchAll(['bar' => 10]);
$list  = Foo::fetchList(['bar' => 10]);
$count = Foo::count(['bar' => 10]);
```

Optinally filter keys may include an directives. The following directives are supported:

Key            | Value  | Description
-------------- | ------ | ---------------------------------------------------
"field"        | scalar | Field is the value
"field (not)"  | scalar | Field is not the value
"field (min)"  | scalar | Field is equal to or greater than the value
"field (max)"  | scalar | Field is equal to or less than the value
"field (any)"  | array  | Field is one of the values in the array
"field (none)" | array  | Field is none of the values in the array

If the field is an array, you may use the following directives

Key            | Value  | Description
-------------- | ------ | ---------------------------------------------------
"field"        | scalar | The value is part of the field
"field (not)"  | scalar | The value is not part of the field
"field (any)"  | array  | Any of the values are part of the field
"field (all)"  | array  | All of the values are part of the field
"field (none)" | array  | None of the values are part of the field

Filters SHOULD be alligned business logic, wich may not directly align to checking a value of a field. A recordset
SHOULD implement a method `filterToQuery` which converts the filter to a DB dependent query statement. You MAY
overload this method to support custom filter keys.

It's save to use query parameters (`$_GET`) and input data (`$_POST`) directly.

    // -> GET /foos?color=red&date(min)=2014-09-01&tags(not)=abandoned&created.user=12345
    
    $result = Foo::fetchAll($_GET);
