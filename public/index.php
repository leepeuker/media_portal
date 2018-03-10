<?php

/**
 * Front controller
 *
 * PHP version 7.0
 */


/**
 * Composer autoload
 */
require dirname(__DIR__) . '/vendor/autoload.php';


/**
 * Set base file paths
 */
define('BASE_DIR', str_replace('\\', '/', dirname(__DIR__)) . '/');
define('APP_DIR',  BASE_DIR . '/app/');
define('PUBLIC_DIR',  BASE_DIR . '/app/');


/**
 * Error and Exception handling
 */
if (Core\Config::get('SHOW_ERRORS') === 1) {
    echo 1;
    $whoops = new \Whoops\Run;
    $whoops->pushHandler(new \Whoops\Handler\PrettyPageHandler);
    $whoops->register();
} else {
    error_reporting(E_ALL); 
    set_error_handler('Core\Error::errorHandler');
    set_exception_handler('Core\Error::exceptionHandler');
}


/**
 * Session
 */
Core\Session::start();


/** 
 * Routing
 */
$router = new Core\Router();

// Add routes
$router->add('/', ['controller' => 'Login', 'action' => 'new']);
$router->add('/logout', ['controller' => 'Logout', 'action' => 'destroy']);
$router->add('/signup', ['controller' => 'Signup', 'action' => 'new']);
$router->add('/movies', ['controller' => 'Movies', 'action' => 'index']);
$router->add('/comics', ['controller' => 'Comics', 'action' => 'index']);
$router->add('/tvshows', ['controller' => 'Tvshows', 'action' => 'index']);
$router->add('/profile', ['controller' => 'Profile', 'action' => 'show']);
$router->add('/admin', ['controller' => 'admin', 'action' => 'index']);
$router->add('/password/reset/{token:[\da-f]+}', ['controller' => 'Password', 'action' => 'reset']);
$router->add('/signup/activate/{token:[\da-f]+}', ['controller' => 'Signup', 'action' => 'activate']);
$router->add('/{controller}/{action}');
$router->add('/{controller}/{action}/{id:\d+}');

// Dispatch the request
$router->dispatch(new Core\Request());