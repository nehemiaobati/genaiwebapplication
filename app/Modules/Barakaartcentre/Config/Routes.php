<?php

namespace App\Modules\Barakaartcentre\Config;

/**
 * @var \CodeIgniter\Router\RouteCollection $routes
 */

// Public Routes
$routes->group('baraka-art-centre', ['namespace' => 'App\Modules\Barakaartcentre\Controllers'], static function ($routes) {
    $routes->get('/', 'PublicController::index', ['as' => 'baraka.home']);
    $routes->post('/', 'PublicController::processPayment');
    $routes->get('about', 'PublicController::about', ['as' => 'baraka.about']);
    $routes->get('services', 'PublicController::services', ['as' => 'baraka.services']);
    $routes->get('science', 'PublicController::science', ['as' => 'baraka.science']);
    $routes->get('gallery', 'PublicController::gallery', ['as' => 'baraka.gallery']);
    $routes->get('workshops', 'PublicController::workshops', ['as' => 'baraka.workshops']);
    $routes->get('contact', 'PublicController::contact', ['as' => 'baraka.contact']);
    $routes->post('newsletter', 'PublicController::signupNewsletter', ['as' => 'baraka.newsletter']);

    // Checkout & Payments
    $routes->get('checkout/artwork/(:num)', 'PublicController::checkoutArtwork/$1', ['as' => 'baraka.checkout.artwork']);
    $routes->get('checkout/workshop/(:num)', 'PublicController::checkoutWorkshop/$1', ['as' => 'baraka.checkout.workshop']);
    $routes->post('checkout/process', 'PublicController::processOrder', ['as' => 'baraka.process.order']);
    $routes->get('payment/verify', 'PublicController::verifyPayment', ['as' => 'baraka.payment.verify']);
});

// Auth Routes
$routes->group('baraka-art-centre/auth', ['namespace' => 'App\Modules\Barakaartcentre\Controllers'], static function ($routes) {
    $routes->get('login', 'AuthController::login', ['as' => 'baraka.login']);
    $routes->post('login', 'AuthController::processLogin');
    $routes->get('logout', 'AuthController::logout', ['as' => 'baraka.logout']);
});

// Admin Routes (Requires Session/Auth Filter)
$routes->group('baraka-art-centre/admin', ['namespace' => 'App\Modules\Barakaartcentre\Controllers', 'filter' => \App\Modules\Barakaartcentre\Filters\AuthFilter::class], static function ($routes) {
    $routes->get('/', 'AdminController::dashboard', ['as' => 'baraka.admin.dashboard']);
    
    // Services CRUD
    $routes->get('services', 'AdminController::services', ['as' => 'baraka.admin.services']);
    $routes->get('services/new', 'AdminController::createService', ['as' => 'baraka.admin.services.create']);
    $routes->post('services/store', 'AdminController::storeService', ['as' => 'baraka.admin.services.store']);
    $routes->get('services/edit/(:num)', 'AdminController::editService/$1', ['as' => 'baraka.admin.services.edit']);
    $routes->post('services/update/(:num)', 'AdminController::updateService/$1', ['as' => 'baraka.admin.services.update']);
    $routes->post('services/delete/(:num)', 'AdminController::deleteService/$1', ['as' => 'baraka.admin.services.delete']);

    // Artworks CRUD
    $routes->get('artworks', 'AdminController::artworks', ['as' => 'baraka.admin.artworks']);
    $routes->get('artworks/new', 'AdminController::createArtwork', ['as' => 'baraka.admin.artworks.create']);
    $routes->post('artworks/store', 'AdminController::storeArtwork', ['as' => 'baraka.admin.artworks.store']);
    $routes->get('artworks/edit/(:num)', 'AdminController::editArtwork/$1', ['as' => 'baraka.admin.artworks.edit']);
    $routes->post('artworks/update/(:num)', 'AdminController::updateArtwork/$1', ['as' => 'baraka.admin.artworks.update']);
    $routes->post('artworks/delete/(:num)', 'AdminController::deleteArtwork/$1', ['as' => 'baraka.admin.artworks.delete']);

    // Workshops CRUD
    $routes->get('workshops', 'AdminController::workshops', ['as' => 'baraka.admin.workshops']);
    $routes->get('workshops/new', 'AdminController::createWorkshop', ['as' => 'baraka.admin.workshops.create']);
    $routes->post('workshops/store', 'AdminController::storeWorkshop', ['as' => 'baraka.admin.workshops.store']);
    $routes->get('workshops/edit/(:num)', 'AdminController::editWorkshop/$1', ['as' => 'baraka.admin.workshops.edit']);
    $routes->post('workshops/update/(:num)', 'AdminController::updateWorkshop/$1', ['as' => 'baraka.admin.workshops.update']);
    $routes->post('workshops/delete/(:num)', 'AdminController::deleteWorkshop/$1', ['as' => 'baraka.admin.workshops.delete']);

    // Signups Management
    $routes->get('signups', 'AdminController::signups', ['as' => 'baraka.admin.signups']);
    $routes->post('signups/delete/(:num)', 'AdminController::deleteSignup/$1', ['as' => 'baraka.admin.signups.delete']);

    // Payments & Orders
    $routes->get('payments', 'AdminController::payments', ['as' => 'baraka.admin.payments']);
    $routes->post('payments/resolve/(:num)', 'AdminController::resolveOrder/$1', ['as' => 'baraka.admin.payments.resolve']);
});
