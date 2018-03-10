<?php

namespace Core;

use PDO;
use Core\Config;

/** 
 * Base model
 *
 * PHP version 7.0
 */
abstract class Model
{

    /**
     * Get the PDO User database connection
     * 
     * @param string DB name
     * @return mixed
     */
    protected static function getDB($dbName)
    {
        static $db = null;
        
        if ($db === null) { 
            $dsn = 'mysql:host=' . Config::get('DB_HOST') . ';dbname=' . $dbName . ';charset=' . Config::get('DB_CHARSET');
            $db = new PDO($dsn, Config::get('DB_USER'), Config::get('DB_PASSWORD'));
            
            // Throw an Exception when an error occurs
            $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        }
         
        return $db;
    }

    /**
     * Convert searchterm for mysql "where like" query
     * 
     * @param string $string String to convert
     * @return string Converted string
     */
    protected static function whereLike($string) 
    {
        return '%' . $string . '%';
    }
}
