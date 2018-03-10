<?php

namespace Utilities;

use \Core\Config;

/**
 * Provides log services
 *
 * PHP version 7.0
 */
class Log
{

    /**
     * Write to custom error log
     * 
     * @param string The message to write in logfile
     * @param string Log type, default INFO
     * @return mixed
     */
    public static function write($message, $type = 'INFO')
    {
        $date = date("Y-m-d H:i:s");
        $headLog = '[' . $date . '] [' . $type . ']: ';

        error_log($headLog . $message . "\n", 3, dirname(__DIR__) . '/logs/test.log');
    }

    /**
     * Log download from user
     * 
     * @param string The file path
     */
    public static function download($fileLocation)
    {
        $date = date("Y-m-d H:i:s");
        $user = Auth::getUser();

        error_log("$date|$user->id|$user->email|$fileLocation\n", 3, BASE_DIR . '/logs/download.log');
    }

    /**
     * Get the log content of the download log
     * 
     * @param string The file path
     */
    public static function getDownload()
    {
        $logLines = explode(PHP_EOL, file_get_contents(BASE_DIR . '/logs/download.log'));

        $downloadLog = array();

        foreach ($logLines as $logLine) {

            if ($logLine != '') {

                array_push($downloadLog, explode('|', $logLine));
            }
        }
        
        return $downloadLog;

    }
}
