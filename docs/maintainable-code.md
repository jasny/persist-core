Maintainable code
---

To create maintainable code you SHOULD at least uphold the following rules:

* Don't access the database outside your model classes.
* Use traits or multiple classes to separate database logic (eg queries) from business.
* Keep the number of `if`s limited. Implement special cases by overloading.

### SOLID
[SOLID](http://en.wikipedia.org/wiki/SOLID_(object-oriented_design)) embodies 5 principles principles that, when 
used together, will make a code base more maintainable over time. While not forcing you to, Jasny DB supports 
building a SOLID code base.

Methods are kept small and each method is expected to be 
[overloaded](http://en.wikipedia.org/wiki/Function_overloading) by extending the class.

Functionality of Jasny DB is defined in interfaces and defined in traits around a single piece of functionality or
design pattern. The use an a specific interface will trigger behaviour. The trait may or may not be used to 
implement the interface without consequence.

### Active Record and SRP
Using the Active Records pattern means you're breaking the
[Single responsibility principle](http://en.wikipedia.org/wiki/Single_responsibility_principle), as active records
combine database logic with business logic in a single class.

This pattern is popular because produces code that is very readable and easy to understand. If you're interrested in
high quality maintainable code, using dependency inject with a data mappers is recommended.

```php
// Active Record
function getUser($id)
{
    $user = User::fetch($id);
    $user->setValues($data);
    $user->save();
}

// Data Mapper
function getUser(DataMapper $users, $id)
{
    $user = $users->fetch($id);
    $user->setValues($data);
    $users->save($user);
}
```
