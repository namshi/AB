# NAMSHI | AB

[![Build Status](https://travis-ci.org/namshi/AB.png?branch=master)](https://travis-ci.org/namshi/AB)

[![SensioLabsInsight](https://insight.sensiolabs.com/projects/82e20b9d-e943-4d19-a9d3-b85a2a48861a/mini.png)](https://insight.sensiolabs.com/projects/82e20b9d-e943-4d19-a9d3-b85a2a48861a)

This library provides a layer to
run AB tests on your applications.
Supports sending AB test data to Google Analytics
for tracking via server-side Content Experiments.

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

Let's say that you are running a test that defines whether
the background color of your website should be black or white.

Once a user hits the homepage, he will get the white one, but
as soon as he refreshes the page he might get the black one!

To be consistent with the variations, for a user's session,
you should store a unique number (seed) and pass it to the
tests before running them, so you will always be sure that
specific user will always get the same variations of the
tests:

``` php
$test = new Test('homepage_color', array(
    'white' => 1,
    'black' => 1,
));

// set the seed
$test->setSeed($_SESSION['seed_for_homepage_color_test']); // 326902637627;

$test->getVariation(); // black
```

In the next request, since the seed won't change,
the user will get again the same variation, `black`.

This functionality is implemented thanks to
PHP's `mt_rand` and `mt_srand` functions.

You shouldn't specify a different seed for each of your
tests, but use the container instead:

``` php
$container = new Container(new Test('homepage_color', array(
    'black' => 1,
    'white' => 1,
)));

$container->setSeed($_SESSION['seed']); // 326902637627;);
```

The advantage of setting the seed through the container is that
you don't have to maintain a seed for every test you run in
the session, you can just use a global seed and the container
will assign a unique seed to each test.

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

## Integrating with Google Analytics: Server-Side Content Experiments

Using Google Analytics (GA) can be an easy way to track the results of AB testing.
If you're AB tests don't use separate URL's, then you can send your AB data to a 
GA server-side Content Experiment setup.

To start, read through 
https://developers.google.com/analytics/solutions/experiments-server-side#store-user
to get a good understanding of how to setup the experiment on GA. During setup,
take note of your Experiment Id and Variation Ids. (Variation Ids are numerically
numbered and start at 0 for the "Original Page")

Once GA is setup:
* Assign your test the GA Experiment Id by passing in a parameter with a key of 'expId'
* Assign your variations their Variation Ids by passing in parameter keys 
equal to the variation value with values equal to that variation's id 


``` php
$test = new Test('my_ab_test', array(
        'a' => 1,
        'b' => 1,
        'c' => 1,
    ),
    array(
        'expId' => 'xyz' // Experiment Id of 'xyz'
        'a' => 0,        // variation 'a' has a GA Variation Id of 0
        'b' => 1,        // variation 'b' has a GA Variation Id of 1
        'c' => 2         // variation 'c' has a GA Variation Id of 2
    )
);

// seed test...

// choose variation...

```

Then, assuming this is the only AB test on this page, dump send GA the results by 
doing the following just below where your analytics.js/tag-manager javascript
is loaded:

``` php
<script>
    // Include analytics.js/tag-manager js here...
    
    <?= Test::getGoogleAnalyticsExperimentJsContent() ?>
</script>
```

Continuing this example, if the chosen variation was variation 'c' it would result in the following generated code:
``` php
<script>
    // Include analytics.js/tag-manager js here...
    
    ga('set', 'expId', 'xyz'); ga('set', 'expVar', '2'); ga('send','pageview');
</script>
```

To reference GA's official documentation see:
https://developers.google.com/analytics/devguides/collection/analyticsjs/experiments#server-example

Important Note: _GA Content Experiments only supports tracking one experiment per page, and so the 
getGoogleAnalyticsExperimentJsContent() method also assumes only one AB test is being ran per page.
Thus it will always return the most recent test's JS Content._

## Test parameters

You can also attach any parameter you want to
a test by just injecting them (or with the `set`
method):

``` php
$test = new Test('example', array(1, 2), array(
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

## Testing this library

This library has been unit tested with
[PHPUnit](http://phpunit.de/manual/current/en/index.html),
so just `cd` into its folder and run `phpunit`.
