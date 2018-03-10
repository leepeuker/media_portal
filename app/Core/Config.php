<?php

namespace Core;

/**
 * Application configuration
 *
 * PHP version 7.0
 */
class Config 
{
    /**
     * Associative array of configuration values
     * @var array
     */
    private static $config = [];

    /**
     * Calls the get() method
     *
     * @param string $key 
     * @return string|array|null
     */
    public static function get($key)
    {
        return self::_get($key);
    }

    /**
     * Set or add a runtime configuration value
     *
     * @param string $key 
     */
    public static function set($key, $value)
    {
        self::_set($key, $value);
    }

    /**
     * Load config file on first method call and get configuration value(s). 
     * 
     * @param string $key 
     * @param string $source 
     * @return string|null
     * @throws Exception if configuration file doesn't exist
     */
    private static function _get($key)
    {

        if (isset(self::$config)) {

            $config_file = BASE_DIR . 'config/config.php';

            if (!file_exists($config_file)) {

                throw new \Exception("Configuration file $config_file doesn't exist");
            }

            self::$config = require $config_file;
        }

        if(empty($key)) {

            return self::$config;

        } else if(isset(self::$config[$key])) {

            return self::$config[$key];
        }

        return null;
    }
}
