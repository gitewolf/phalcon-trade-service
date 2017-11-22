<?php
/**
 * Register application modules
 */

$application->registerModules(
    [
        'api'  => [
            'className' => 'Api\Module',
            'path'      => __DIR__ . '/../controllers/Module.php'
        ],
        'backend' => [
            'className' => 'Backend\Module',
            'path'      => __DIR__ . '/../modules/backend/Module.php'
        ]
    ]
);
