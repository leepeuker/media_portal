<?php

namespace Core;

/**
 * Provides access to the http request
 *
 * PHP version 7.0
 */
class Request
{
    /**
     * POST data as well as uploaded files
     * @var array
     */
    private $data = [];

    /**
     * Query string arguments
     * @var array
     */
    private $query = [];

    /**
     * Parameters from the matched route
     * @var array
     */
    public $params = [
        "controller" => null, 
        "action"  => null, 
        "args"  => null
    ];

    /**
     * The URL used to make the request.
     * @var string
     */
    private $url = null;

    /**
     * Constructor
    */
    public function __construct()
    {
        $this->data = $this->mergeData($_POST, $_FILES);
        $this->query = $_GET;
        $this->url = $this->url();
    }

    /**
     * merge post and files data
     * You shouldn't have two fields with the same 'name' attribute in $_POST & $_FILES
     *
     * @param array $post
     * @param array $files
     * @return array the merged array
     */
    private function mergeData(array $post, array $files)
    {
        foreach($post as $key => $value) {

            if(is_string($value)) {
                $post[$key] = trim($value); 
            }
        }

        return array_merge($files, $post);
    }

    /**
     * Count fields in $this->data and optionally exclude some fields
     *
     * @param array $exclude Fields to exclude
     * @return mixed
     */
    public function countData(array $exclude = [])
    {
        $count = count($this->data);

        foreach($exclude as $field){

            if(array_key_exists($field, $this->data)){
                $count--;
            }
        }

        return $count;
    }

    /**
     * Safer and better access to $this->data
     *
     * @param string $key Key name of data value
     * @return mixed 
     */
    public function data($key = null)
    {
        if ($key = null) {

            return $this->data;
        }

        return array_key_exists($key, $this->data) ? $this->data[$key] : null;
    }

    /**
     * Safer and better access to $this->query
     *
     * @param string $key Key name of query value
     * @return mixed
     */
    public function query($key = null)
    {
        if ($key = null) {

            return $this->data;
        }

        return array_key_exists($key, $this->query) ? $this->query[$key] : null;
    }

    /**
     * safer and better access to $this->params
     *
     * @param string $key  Key name of param value
     * @return mixed
     */
    public function params($key)
    {
        return array_key_exists($key, $this->params) ? $this->params[$key] : null;
    }

    /**
     * detect if request is Ajax
     *
     * @return boolean True if ajax request, false otherwise
     */
    public function isAjax()
    {
        if (!empty($_SERVER['HTTP_X_REQUESTED_WITH'])) {

            return strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest';
        }

        return false;
    }

    /**
     * detect if request is POST request
     *
     * @return boolean True if post request, false otherwise
     */
    public function isPost()
    {
        return $_SERVER["REQUEST_METHOD"] === "POST";
    }

    /**
     * detect if request is GET request
     *
     * @return boolean True if get request, false otherwise
     */
    public function isGet()
    {
        return $_SERVER["REQUEST_METHOD"] === "GET";
    }

    /**
     * Detect if request over secured connection(SSL)
     *
     * @return boolean True if ssl request, false otherwise
     *
     */
    public function isSSL()
    {
        return isset($_SERVER['HTTPS']) && !empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== "off";
    }

    /**
     * Get content length
     *
     * @return integer
     */
    public function contentLength()
    {
        return (int)$_SERVER['CONTENT_LENGTH'];
    }

    /**
     * Get the current uri of the request (without query variables)
     *
     * @return string|null
     */
    public function uri()
    {
        $uri = '/';

        if (!empty($_SERVER['QUERY_STRING'])) {
            $parts = explode('&', $_SERVER['QUERY_STRING'], 2);

            if (strpos($parts[0], '=') === false) {
                $uri .= rtrim($parts[0], '/');
            }
        }

        return $uri;
    }

    /**
     * Get the name of the server host
     *
     * @return string|null
     */
    public function name()
    {
        return isset($_SERVER['SERVER_NAME']) ? $_SERVER['SERVER_NAME'] : null;
    }

    /**
     * Get the referer of this request.
     *
     * @return string|null
     */
    public function referer()
    {
        return isset($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : null;
    }

    /**
     * Fet the client IP addresses.
     *
     * @return string|null
     */
    public function clientIp()
    {
        return isset($_SERVER['REMOTE_ADDR'])? $_SERVER['REMOTE_ADDR']: null;
    }

    /**
     * Get the contents of the User Agent
     *
     * @return string|null
     */
    public function userAgent()
    {
        return isset($_SERVER['HTTP_USER_AGENT'])? $_SERVER['HTTP_USER_AGENT']: null;
    }

    /**
     * Get the request protocol.
     *
     * @return string
     */
    public function protocol()
    {
        return $this->isSSL() ? 'https' : 'http';
    }

    /**
     * Get the protocol and HTTP host
     *
     * @return string The protocol and the host
     */
    public function getProtocolAndHost()
    {
        return $this->protocol() . '://' . $_SERVER['SERVER_NAME'];
    }

    /**
     * Get the full URL for the request with the added query string parameters
     *
     * @return string
     */
    public function url()
    {
        return $this->getProtocolAndHost() . $this->uri();
    }

    /**
     * Get the full URL for the request without the protocol.
     * 
     * It could be useful to force a specific protocol.
     *
     * @return string
     */
    public function urlWithoutProtocol()
    {
        return preg_replace('#^https?://#', '', $this->url());
    }

    /**
     * Returns the base URL.
     *
     * Examples:
     *  * http://localhost/                         returns an empty string
     *  * http://localhost/test/public/user         returns test
     *  * http://localhost/test/posts/view/123      returns test
     *
     * @return string
     */
    public function getBaseUrl()
    {
        $baseUrl = str_replace(['public', '\\'], ['', '/'], dirname($_SERVER['SCRIPT_NAME']));
        return $baseUrl;
    }

    /**
     * Get the root URL for the application.
     *
     * @return string
     */
    public function root()
    {
        return $this->getProtocolAndHost() . $this->getBaseUrl();
    }
}