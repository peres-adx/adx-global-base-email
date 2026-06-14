<?php

use CodeIgniter\Router\RouteCollection;

$routes->get('/', 'Home::index');

$routes->options('login',								'AuthController::options');
$routes->post('login',									'AuthController::login');

$routes->options('api/tests/audit',     'AuthController::options');
$routes->get('api/tests/audit',         'AuditController::run');

$routes->options('api/setup/master',		'AuthController::options');
$routes->post('api/setup/master',				'UserController::setupMaster');

$routes->group('api/users', ['filter' => 'auth'], function($routes) {

  $routes->options('(:any)',						'AuthController::options');
  $routes->get('/',											'UserController::listAll');
  $routes->get('(:segment)',						'UserController::listById/$1');
  $routes->post('/',										'UserController::register');
  $routes->put('(:segment)',						'UserController::update/$1');
  $routes->delete('(:segment)',					'UserController::delete/$1');
  $routes->post('change-password',			'UserController::changePassword');  

});

$routes->group('api/customers', ['filter' => 'auth'], function($routes) {

  $routes->options('(:any)',						'AuthController::options');
  $routes->get('/',											'CustomerController::listAll');
  $routes->get('user/(:segment)',				'CustomerController::listByUser/$1');
  $routes->get('(:segment)',						'CustomerController::listById/$1');
  $routes->post('/',										'CustomerController::register');
  $routes->put('(:segment)',						'CustomerController::update/$1');
  $routes->delete('(:segment)',					'CustomerController::delete/$1');

});

$routes->group('api/orders', ['filter' => 'auth'], function($routes) {

  $routes->options('(:any)',						'AuthController::options');
  $routes->get('/',											'OrderController::listAll');
  $routes->get('(:segment)',						'OrderController::listById/$1');
  $routes->get('customer/(:segment)',		'OrderController::listByCustomer/$1');
  $routes->post('/',										'OrderController::register');
  $routes->put('(:segment)',						'OrderController::update/$1');
  $routes->delete('(:segment)',					'OrderController::delete/$1');

});