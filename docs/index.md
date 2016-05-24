Jasny DB
========

Jasny DB adds OOP design patterns to PHP's database extensions.

* [Service locator](service-locator.md)
* [Connection](connection.md)
* [Entity](entity.md)
* [Data mapper](mapper.md)
* [Dataset](dataset.md)
* [Entity set](entity-set.md)
* [Metadata](meta-data.md)
* Entity traits
<ul>
  * [Type casting](entity/type-casting.md)
  * [Identifiable](entity/identifiable.md)
  * [Self aware](entity/self-aware.md)
  * [Change aware](entity/change-aware.md)
  * [Active record](entity/active-record.md)
  * [Dynamic](entity/dynamic.md)
  * [Enrichable](entity/enrichable.md)
  * [Redactable](entity/redactable.md)
  * [Lazy loading](entity/lazy-loading.md)
  * [Soft deletion](entity/soft-deletion.md)
</ul>
* [Maintainable code](maintainable-code.md)
* [Code generation](code-generation.md)

Jasny DB is a [data access layer](https://en.wikipedia.org/wiki/Data_access_layer) (*not* a DB abstraction layer) for
PHP. It does allow you properly structure your model, while still using the methods and functionality of PHP's native
database extensions.

### Installation
This library is not intended to be installed directly. The Jasny DB library contains design pattern definitions and
implementations. It serves as an abstract base for concrete libraries implemented for specific PHP extensions.

### Implementations

* [Jasny\DB\MySQL](http://github.com/jasny/db-mysql) extends [mysqli](http://php.net/mysqli)
* [Jasny\DB\Mongo](http://github.com/jasny/db-mongo) extends [mongo](http://php.net/mongo)
* [Jasny\DB\REST](http://github.com/jasny/db-rest) for datasources implementing
  [REST](http://en.wikipedia.org/wiki/Representational_state_transfer)
