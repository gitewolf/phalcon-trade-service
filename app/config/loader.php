<?php
$loader = new \Phalcon\Loader();
$loader->register();

/**
 * We're a registering a set of directories taken from the configuration file
 */

$loader->registerNamespaces(array(
    'Controllers' => __DIR__ . '/../controllers/',
    'Models\Store' => __DIR__ . '/../models/store/',
    'Models\Pro' => __DIR__ . '/../models/pro/',
    'Services' => __DIR__ . '/../services/',
    'Common' => __DIR__ . '/../common/',
    'Exceptions' => __DIR__ . '/../exceptions/',
));


// Use composer autoloader to load vendor classes
require_once __DIR__ . '/../../vendor/autoload.php';
$loader->register();





