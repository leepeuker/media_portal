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
error_reporting(E_ALL); 
set_error_handler('Core\Error::errorHandler');
set_exception_handler('Core\Error::exceptionHandler');


/**
 * Session
 */
ini_set('session.name', 'id'); // Specifies the name of the session which is used as cookie name.
ini_set('session.cookie_secure', true); // Allows access to session ID cookie only when protocol is HTTPS.
ini_set('session.cookie_httponly', true); // Disallows access to session cookie by JavaScript.
ini_set('session.use_strict_mode', true); // Prevents session module to use uninitialized session ID.
ini_set('session.use_only_cookies', true); // Forces sessions to only use cookie to store the session ID on the client side.
ini_set('session.cache_limiter', 'nocache'); // Makes sure HTTP contents are not cached for authenticated session.
ini_set('session.entropy_file', '/dev/urandom'); // Gives a path to an external resource, which will be used as an additional entropy source in the session id creation process.
ini_set('session.entropy_length', '256'); // Specifies the number of bytes which will be read from the session.entropy_file.
ini_set('session.hash_function', 'sha256'); // Specifies the hash algorithm used to generate the session IDs.
ini_set('session.gc_maxlifetime', 0); // Specifies the number of seconds after which data will be seen as 'garbage' and potentially cleaned up..
session_start();

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