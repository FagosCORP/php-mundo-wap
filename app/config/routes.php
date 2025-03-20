<?php

use Cake\Routing\Route\DashedRoute;
use Cake\Routing\RouteBuilder;

return static function (RouteBuilder $routes) {

    $routes->setRouteClass(DashedRoute::class);
    $routes->setExtensions(['json']);

    $routes->scope('/visits', function (RouteBuilder $routes) {
        $routes->get('/by-date', ['controller' => 'Visits', 'action' => 'byDate']);

        $routes->put('/{id}', ['controller' => 'Visits', 'action' => 'edit'])
            ->setPatterns(['id' => '\d+'])
            ->setPass(['id']);

        $routes->post('/', ['controller' => 'Visits', 'action' => 'add']);
    });

    $routes->scope('/workdays', function (RouteBuilder $routes) {
        $routes->post('/close-day', ['controller' => 'Workdays', 'action' => 'closeDay']);
        $routes->get('/', ['controller' => 'Workdays', 'action' => 'index']);
    });
    $routes->fallbacks();
};
