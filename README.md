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
$container->add(new Test('checkout_button_text', array(
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

Variations' weight must be expressed in absolute values: if, for
example, you have `A: 1`, `B: 2` and `C: 1`, that means that the
percentage of picking each variation is 25% (A), 50% (B) and
25%(C), as the sum of the weights is 4.

Variations can be set while constructing the test or later on:

``` php
$test = new Test('checkout_button_text', array(
    'Buy now!'          => 1,
    'Go to checkout!'   => 1,
));

// or you can set them afterwards
$test = new Test('checkout_button_text');

$test->setVariations(array(
    'Buy now!'          => 1,
    'Go to checkout!'   => 1,
));
```

Remember to set the variations before running the test
with `getVariation`, else an exception is thrown:

``` php
$test = new Test('checkout_button_text');

$test->getVariation(); // will throw a BadMethodCallException
```

## How to present the same variations across multiple requests

## Disabling tests

Sometimes you might want to disable tests for different purposes,
for example if the user agent who is visiting the page is a bot.

``` php
$test = new Test('my_ab_test', array(
    'a' => 0,
    'b' => 1,
));

$test->disable();

$test->getVariation(); // will return 'a'!
```

Once you disable the test and run it, it **will
always return the first variation**, no matter what
its odds are! Yes, even zero...

## Tracking name

Let's say that you are using an AB test tracking
tool that lets you define a test and registers the
results: it might be that this tool requires you
to register the result via HTTP calls using the test
ID as per their format (like: `longCluelessString123456`).

At the same time you don't want to have, within
your code, names that are not meaningful:  that's why
we've added a **tracking name** that you can set for
each test.

``` php
$trackingName = "iu23r4b3rib3rb3rb3ib";

$test = new Test('checkout_button_text', array(
    'Buy now!'              => 1,
    'Buy immediately man!'  => 3,
), $trackingName);
```

At that point, you can still reference the test, in the
container, with **your** name:

``` php
$testContainer->get('checkout_button_text')->getVariation();
```

and when you need to report the result to your external
tool, you can use the actual tracking identifier:

``` php
$httpClient->makeRequest('http://api.yourtool.com/register/' . $test->getTrackingName() . '/' . $test->getVariation());
```

## Test parameters

You can also attach any parameter you want to
a test by just injecting them (or with the `set`
method):

``` php
$test = new Test('example', array(1, 2), 'tracking_name', array(
    'par1' => 1,
    'par2' => new stdClass,
));

$test->set('par3', 'Whoooops!')
```

So that you can then easily retrieve them in other parts of
the code:

``` php
$test->getParameters(); // returns all the parameters

$test->get('par3'); // Whoooops!

$test->get('i-dont-exist'); // NULL
```

## How odds internally work

When you assign variations with their odds to a test,
you must specify absolute values, but internally the
test converts them into `Odd` objects which have
a percentage-based value, with minimum and maximum limit.

WAIT WHAT?

The limits are used to then pick one variation among all
the others: for example, when you specify 2 variations, `A`
with odds 1 and `B` with odds 2, the `Test` converts them
into objects with the folowing properties:

* A
    * value:    33 (%)
    * min:      0
    * max:      33
* B
    * value:    66 (%)
    * min:      34
    * max:      100

This should make the entire concept a little bit
clearer.

When you call `getVariation` for the first time,
the test runs and picks the variation by generating
a random number between 1 and 100, and getting the
variation that "contains" that number.

For example, this is what you would get, from the
example above, with this list of random numbers:

* 1:    `A`
* 100:  `B`
* 50:   `B`
* 33:   `A`
* 34:   `B`

## Testing this library

This library has been unit tested with
[PHPUnit](http://phpunit.de/manual/current/en/index.html),
so just `cd` into its folder and run `phpunit`.