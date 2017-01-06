# Maintainable code

To create maintainable code you SHOULD at least uphold the following rules:

* Don't access the database outside your model classes.
* Use traits or multiple classes to separate database logic (eg queries) from business.
* Keep the number of `if`s limited. Implement special cases by overloading.

## SOLID

[SOLID][] embodies 5 principles principles that, when used together, will make a code base more maintainable over time.
While not forcing you to, Jasny DB supports building a SOLID code base.

* [Single responsibility principle][]
* [Open/closed principle][]
* [Liskov substitution principle][]
* [Interface segregation principle][]
* [Dependency inversion principle][]

## Interfaces and traits

Functionality of Jasny DB is defined in interfaces and defined in traits around a single piece of functionality or
design pattern. The use an a specific interface will trigger behaviour. Traits may or may not be used to implement the
interface without consequence.

## Base classes

In the `Jasny\DB\Base` namespace, there are objects that implement these interfaces using the traits. Using and
extending these objects, makes it easier to use the library.

Rather than using these base objects, you may choose to create you're own base classes. Dependency is always on
interfaces, never on classes.


[SOLID]: http://en.wikipedia.org/wiki/SOLID_(object-oriented_design)
[Single responsibility principle]: https://en.wikipedia.org/wiki/Single_responsibility_principle
[Open/closed principle]: https://en.wikipedia.org/wiki/Open/closed_principle
[Liskov substitution principle]: https://en.wikipedia.org/wiki/Liskov_substitution_principle
[Interface segregation principle]: https://en.wikipedia.org/wiki/Interface_segregation_principle
[Dependency inversion principle]: https://en.wikipedia.org/wiki/Dependency_inversion_principle

