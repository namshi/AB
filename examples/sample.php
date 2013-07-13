<?php

require __DIR__ . '/../vendor/autoload.php';

use Namshi\AB\Test;
use Namshi\AB\Container;

session_start();

if (!isset($_SESSION['seed'])) {
    $_SESSION['seed'] = mt_rand();
}

$abt = new Container(array(
    new Test('greet', array(
        'Hey dude!' => 1,
        'Welcome'   => 1,
    )),
    new Test('background-color', array(
        'yellow'    => 1,
        'white'     => 1,
    )),
), $_SESSION['seed']);

?>

<html>
    <head>
        <style>
            * {
                background-color: <?php echo $abt['background-color']->getVariation(); ?>;
            }
        </style>
    </head>
    <body>
        <h1>
            <?php echo $abt['greet']->getVariation(); ?>
        </h1>
        
        <div>
            Your seed is <?php echo $_SESSION['seed']; ?>
        </div>
    </body>
</html>