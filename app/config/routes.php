<?php
/*
 * Define custom routes. File gets included in the router service definition.
 */
$router = new Phalcon\Mvc\Router();
$request = new \Phalcon\Http\Request();
$method = $request->getMethod();
$restfulActionArr = [
    'GET' => 'detail',
    'POST' => 'create',
    'PUT' => 'update',
    'DELETE' => 'delete',
];
if(!in_array($method,array_keys($restfulActionArr))){
    $method = 'GET';
}
//
//$router->setDefaultModule("api");
//$router->setDefaultNamespace("Controllers");

//restful 路由
$router->add(
    '/:controller',
    array(
        'namespace'  => 'Controllers',
        'module' => 'api',
        'controller' => 1,
        'action'     => $restfulActionArr[$method]
    )
);

$router->add(
    '/',
    array(
        'namespace'  => 'Controllers',
        'module' => 'api',
        'controller' => 'index'
    )
);
$router->add(
    '/:controller/:action',
    array(
        'namespace'  => 'Controllers',
        'module' => 'api',
        'controller' => 1,
        'action'     => 2,
    )
);
$router->add(
    '/:controller/:action/:param',
    array(
        'namespace'  => 'Controllers',
        'module' => 'api',
        'controller' => 1,
        'action'     => 2,
        'param'     => 3,
    )
);

//    $router->add(
//        '/fd/:controller',
//        array(
//            'namespace'  => 'Api\Controllers',
//            'module' => 'api',
//            'controller' => 1
//        )
//    );
//    $router->add(
//        '/fd/:controller/:action',
//        array(
//            'namespace'  => 'Api\Controllers',
//            'module' => 'api',
//            'controller' => 1,
//            'action'     => 2,
//        )
//    );
//    $router->add(
//        '/fd/:controller/:action/:param',
//        array(
//            'namespace'  => 'Api\Controllers',
//            'module' => 'api',
//            'controller' => 1,
//            'action'     => 2,
//            'param'     => 3,
//        )
//    );


$router->add(
    '/bm/:controller',
    array(
        'namespace'  => 'Backend\Controllers',
        'module' => 'backend',
        'controller' => 1
    )
);
$router->add(
    '/bm/:controller/:action',
    array(
        'namespace'  => 'Backend\Controllers',
        'module' => 'backend',
        'controller' => 1,
        'action'     => 2,
    )
);
$router->add(
    '/bm/:controller/:action/:param',
    array(
        'namespace'  => 'Backend\Controllers',
        'module' => 'backend',
        'controller' => 1,
        'action'     => 2,
        'param'     => 3,
    )
);
//    $router->add(
//        '/backend/:controller/:action',
//        array(
//            'namespace'  => 'Backend\Controllers',
//            'controller' => 1,
//            'action'     => 2,
//        )
//    );

$router->setUriSource(\Phalcon\Mvc\Router::URI_SOURCE_SERVER_REQUEST_URI);

$router->removeExtraSlashes(true);

return $router;

return $router;
