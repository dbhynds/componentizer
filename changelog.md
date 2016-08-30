# Changelog

## 1.4.0

* If no controller is found, `components_build` tries to `simple_render` the component.
* Optionally, pass a post ID to the `simple_render` method on `Context`.
* Optionally, pass a twig to a new `Context`.

## 1.3.0

* Use Composer autoloader!

## 1.2.0

* Added a `simple_render` method on `Context` for if you just want to get the fields and send them straight to the twig.
* Cleaned up the documentation substantially.

## 1.1.1

* Methods `render` and `compile` on `Context` no longer require a twig as an argument. If called, they will fetch a twig matching the name of the file that called the method.

## 1.1.0

* Method `set` on `Context` no longer accepts key value pairs because of recursive problem with arrays.
* Fixed label on admin screen

## 1.0.0

* Redid componentizer to be object oriented
* Added a `Componentizer` namespace