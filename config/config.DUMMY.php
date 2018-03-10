<?php

 /**
  * This file contains configuration for the application.
  * It will be used by app/Core/Config.php
  *
  * PHP version 7.0
  */

return array(

    /**
     * Configuration for: Database Connection
     * Define database constants to establish a connection.
     */
    'DB_HOST' => '',
    'DB_NAME_USER' => '',
    'DB_NAME_KODI' => '',
    'DB_NAME_MEDIA' => '',
    'DB_USER' => '',
    'DB_PASSWORD' => '',
    'DB_CHARSET' => 'utf8',

    /**
     * Configuration for: Email server credentials
     *
     */
    'EMAIL_SMTP_DEBUG' => 0,
    'SMTP_HOST' => '',
    'SMTP_PORT' => 465,
    'SMTP_Auth' => true,
    'SMTP_USER' => '',
    'SMTP_PASSWORD' => '',
    'SMTP_SECURE' => 'ssl',
    'EMAIL_WEBMASTER' => '',


    /**
     * Configuration for: Cookies
     *
     * COOKIE_RUNTIME: How long should a cookie be valid by seconds.
     * COOKIE_DOMAIN: The domain where the cookie is valid for.
     * COOKIE_PATH: The path where the cookie is valid for. If set to '/', the cookie will be available within the entire COOKIE_DOMAIN.
     * COOKIE_SECURE: If the cookie will be transferred through secured connection(SSL).
     * COOKIE_HTTP: If set to true, Cookies that can't be accessed by JS
     * COOKIE_SECRET_KEY: A random value to make the cookie more secure.
     *
     */
    'COOKIE_EXPIRY' => 1209600,
    'SESSION_COOKIE_EXPIRY' => 604800,
    'COOKIE_DOMAIN' => '',
    'COOKIE_PATH' => '/',
    'COOKIE_SECURE' => false,
    'COOKIE_HTTP' => true,
    'COOKIE_SECRET_KEY' => '',

    /**
     * Configuration for: Encryption Keys
     */
    'SECRET_KEY' => '',


    /**
     * Configuration for: Misc
     */
    'SHOW_ERRORS' => 1
);