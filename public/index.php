<?php
use Phalcon\Mvc\Application;

error_reporting(E_ALL);
ini_set('display_errors', 1);

if ((isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] == 'on') || (isset($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] == 'https')) {
    define('APP_HTTP_SCHEME', 'https://');
} else {
    define('APP_HTTP_SCHEME', 'http://');
}

try {


    /**
     * Include header.php
     */
    require __DIR__ . '/../app/config/header.php';
    /**
     * Include loader
     */
    require __DIR__ . '/../app/config/loader.php';

    /**
     * Include services
     */
    require __DIR__ . '/../app/config/services.php';

    /**
     * Handle the request
     */
    $application = new Application();

    /**
     * Assign the DI
     */
    $application->setDI($di);

    /**
     * Include modules
     */
    require __DIR__ . '/../app/config/modules.php';


// Use composer autoloader to load vendor classes
    require_once __DIR__ . '/../vendor/autoload.php';

    echo $application->handle()->getContent();
} catch (Phalcon\Exception $e) {
    echo $e->getMessage();
} catch (PDOException $e) {
    echo $e->getMessage();
}


