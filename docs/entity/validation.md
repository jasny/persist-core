Validation
---

Entities implementing the Validatable interface, can do some basic validation prior to saving them. This includes
checking that all required properties have values, checking the variable type matches and checking if values are
uniquely present in the database.

The `validate()` method will return a [`Jasny\ValidationResult`](https://github.com/jasny/validation-result#readme).

```php
$validation = $entity->validate();

if ($validation->failed()) {
    http_response_code(400); // Bad Request
    json_encode($validation->getErrors());
    exit();
}
```

### Metadata

An entity represents an element in the model. The [metadata](http://en.wikipedia.org/wiki/Metadata) holds 
information about the structure of the entity. Metadata should be considered static as it describes all the
entities of a certain type.

Metadata for a class might contain the table name where data should be stored. Metadata for a property might 
contain the data type, whether or not it is required and the property description.

Jasny DB support defining metadata through annotations by using [Jasny\Meta](http://www.github.com/jasny/meta).

```php
/**
 * User entity
 *
 * @entitySet UserSet
 */
class User
{
   /**
    * @var string
    * @required
    */
   public $name;
}
```

### Class annotations

    * @entitySet - Default entity set for this class of Entities

_Additional class annotations may be used by a specific Jasny DB driver._

### Property annotations

    * @var - (type casting) - Value type or class name
    * @type - (validation) - Value (sub)type
    * @required (validation) - Value should not be blank at validation.
    * @min (validation) - Minimal value
    * @max (validation) - Maximal value
    * @minLength (validation) - Minimal length of a string
    * @maxLength (validation) - Maximal length of a string
    * @options _values_ (validation) - Value should be one the the given options.
    * @pattern _regex_ (validation) - Value should match the regex pattern.
    * @immutable (validation) - Property can't be changed after it is created.
    * @unique (validation) - Entity should be unique accross it's dataset.
    * @unique _field_ (validation) - Entity should be unique for a group. The group is identified by _field_.
    * @censor (redact) - Skip property when outputting the entity.

_Additional property annotations may be used by a specific Jasny DB driver._

### Caveat
Metadata can be really powerfull in generalizing and abstracting code. However you can quickly fall into the trap of
coding through metadata. This tends to lead to code that's hard to read and maintain.

Only use the metadata to abstract widely use functionality and use overloading to implement special cases.
