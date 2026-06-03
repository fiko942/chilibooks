<?php

use CodeIgniter\Router\RouteCollection;

/**
 * @var RouteCollection $routes
 */
$routes->get('/', 'Home::index');
$routes->get('login', 'AuthController::login');
$routes->get('faq', 'AuthController::faq');
$routes->get('invoice/(:num)', 'TransactionController::publicInvoice/$1');
$routes->post('login', 'AuthController::attemptLogin');
$routes->post('logout', 'AuthController::logout');

$routes->group('', ['filter' => 'auth'], static function ($routes) {
    $routes->get('dashboard', 'DashboardController::index');

    $routes->get('products', 'ProductController::index');
    $routes->get('products/new', 'ProductController::new');
    $routes->post('products', 'ProductController::create');
    $routes->get('products/edit/(:num)', 'ProductController::edit/$1');
    $routes->post('products/update/(:num)', 'ProductController::update/$1');
    $routes->post('products/delete/(:num)', 'ProductController::delete/$1');

    $routes->get('customers', 'CustomerController::index');
    $routes->get('customers/new', 'CustomerController::new');
    $routes->get('customers/(:num)', 'CustomerController::show/$1');
    $routes->post('customers', 'CustomerController::create');
    $routes->post('customers/update/(:num)', 'CustomerController::update/$1');
    $routes->post('customers/delete/(:num)', 'CustomerController::delete/$1');

    $routes->get('transactions', 'TransactionController::index');
    $routes->get('transactions/new', 'TransactionController::new');
    $routes->post('transactions', 'TransactionController::create');
    $routes->get('transactions/send-invoice/(:num)', 'TransactionController::sendInvoice/$1');
    $routes->get('transactions/(:num)', 'TransactionController::show/$1');
    $routes->get('transactions/proof/(:num)', 'TransactionController::proof/$1');
    $routes->post('transactions/cancel/(:num)', 'TransactionController::cancel/$1');

    $routes->get('reports', 'ReportController::index');
    $routes->get('reports/pdf', 'ReportController::pdf');
    $routes->get('reports/excel', 'ReportController::excel');
});
