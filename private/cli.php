<?php

use Phalcon\DI\FactoryDefault\CLI as CliDI;
use Phalcon\CLI\Console as ConsoleApp;

define('VERSION', '1.0.0');


// 定义应用目录路径
defined('APPLICATION_PATH')
|| define('APPLICATION_PATH', realpath(dirname(dirname(__FILE__))));
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
require __DIR__ . '/../app/config/cli_services.php';


// 创建console应用
$console = new ConsoleApp();
$console->setDI($di);
$loader->registerDirs(
    array(
        APPLICATION_PATH . '/app/tasks'
    )
);
$loader->register();
/**
 * 处理console应用参数
 */
$arguments = array();
foreach ($argv as $k => $arg) {
    if ($k == 1) {
        $arguments['task'] = $arg;
    } elseif ($k == 2) {
        $arguments['action'] = $arg;
    } elseif ($k >= 3) {
        $arguments['params'][] = $arg;
    }
}

// 定义全局的参数， 设定当前任务及动作
define('CURRENT_TASK',   (isset($argv[1]) ? $argv[1] : null));
define('CURRENT_ACTION', (isset($argv[2]) ? $argv[2] : null));

try {
    // 处理参数
    $console->handle($arguments);
} catch (\Phalcon\Exception $e) {
    echo $e->getMessage();
    exit(255);
}
