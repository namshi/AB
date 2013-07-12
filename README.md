# NAMSHI | AB

This library provides a layer to
run AB tests on your applications.

AB testing is useful when you want
to change anything on your application
but want to benchmark different variations
of the change (ie. display a button
that says "Buy now", "Go to checkout" or
"PAY, DUDE!").

## Installation

This library is available through composer,
as you can see from its
[packagist page](https://packagist.org/packages/namshi/ab).

Just require it in your `composer.json`:

```
"namshi/ab": "1.0.*"
```

## Creating and running a test

Creating tests is very simple, as you only need to
define the test name and the variations, with their
absolute probability:

``` php
use Namshi\AB\Test;

$homepageColorTest = new Test('homepage_color', array(
    'blue' => 1,
    'red' => 1,
));
```

and at this point you can change the color of the
homepage by simply running the test and checking
which variation has been picked:

``` php
<html>
  ...
  ...
  <body style="background-color: <?php echo $homepageColorTest->getVariation(); ?>">
```

Of course, the mess of a code above is here just
as an example ;-)

## Handling multiple tests

Taken by an AB-test-rage, you might want
to start using AB tests for everything:
that's why we added a test container where
you can register as much tests as you want,
and retrieve them easily:

``` php
use Namshi\AB\Container;
use Namshi\AB\Test;

// instantiate the container with a test
$container = new Container(array(
    new Test('homepage_color', array(
        'blue'  => 1,
        'black' => 2,
    )),
));

// add another test
$container->add(new Test('checkout_button_test', array(
    'Buy now'               => 4,
    'Go to checkout'        => 1,
    'Proceed to checkout'   => 1,
)));

// then you can retrieve all the tests
$container->getAll();

// or a single test, by its name
$container->get('checkout_button_text');
```

The `Container` implements the `ArrayAccess` and
`Countable` interfaces, so that you can access its
tests like if it is an array:

``` php
$tests = $container; // I only do this for readability

foreach($tests as $test) {
    echo sprintf("Test '%s' has the variation %s", $test->getName(), $test->getVariation());
}

// if you have time to waste, count the tests :)
echo count($tests);
```

## Variations

## How to present the same variations across multiple requests

## Disabling tests

## Tracking name

## Test parameters

## How odds internally work

## Testing this library