<?php

namespace Api;

use Phalcon\Loader;
use Phalcon\Mvc\View;
use Phalcon\DiInterface;
use Phalcon\Mvc\ModuleDefinitionInterface;
use Phalcon\Db\Adapter\Pdo\Mysql as DbAdapter;

class Module implements ModuleDefinitionInterface
{
    /**
     * Registers the module auto-loader
     *
     * @param DiInterface $di
     */
    public function registerAutoloaders(DiInterface $di = null)
    {
        $loader = new Loader();

        $loader->registerNamespaces(
            [
//                'Controllers' => __DIR__ . '/../controllers/',
//                'Models\Entities' => __DIR__ . '/../models/entities/',
//                'Models\Sns' => __DIR__ . '/../models/sns/',
//                'Services' => __DIR__ . '/../services/',
//                'Common' => __DIR__ . '/../common/',
//                'Exceptions' => __DIR__ . '/../exceptions/',
            ]
        );

        $loader->register();
    }

    /**
     * Registers the module-only services
     *
     * @param DiInterface $di
     */
    public function registerServices(DiInterface $di)
    {
        /**
         * Setting up the view component
         */
        $di['view'] = function () {
            $view = new View();
            $view->setViewsDir(__DIR__ . '/views/');

            return $view;
        };

//        /**
//         * Database connection is created based in the parameters defined in the configuration file
//         */
//        $di['db'] = function () use ($config) {
//            return new DbAdapter(
//                [
//                    "host" => $config->db->host,
//                    "username" => $config->db->username,
//                    "password" => $config->db->password,
//                    "dbname" => $config->db->dbname
//                ]
//            );
//        };
    }
}
