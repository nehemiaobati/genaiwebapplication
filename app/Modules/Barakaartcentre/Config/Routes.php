<?php

namespace App\Modules\Barakaartcentre\Config;

/**
 * @var \CodeIgniter\Router\RouteCollection $routes
 */

$routes->group('baraka-art-centre', ['namespace' => 'App\Modules\Barakaartcentre\Controllers'], static function ($routes) {
    $routes->get('/', 'BarakaartcentreController::index', ['as' => 'baraka.index']);
    $routes->post('/', 'BarakaartcentreController::index');
});
