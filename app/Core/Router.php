<?php

namespace Core;

/**
 * Routes the http request and calls the matching controller/action method
 *
 * PHP version 7.0
 */
class Router
{
    /**
     * Request
     * @var Request
     */
    protected $request = null;

    /**
     * Associative array of routes (the routing table)
     * @var array
     */
    protected $routes = [];

    /**
     * Add a route to the routing table
     *
     * @param string $route The route URL
     * @param array  $params Parameters for the route (controller, action, etc.)
     * @return void
     */
    public function add($route, $params = [])
    {
        // Convert the route to a regular expression: escape forward slashes
        $route = preg_replace('/\//', '\\/', $route);

        // Convert variables e.g. {controller}
        $route = preg_replace('/\{([a-z]+)\}/', '(?P<\1>[a-z-]+)', $route);

        // Convert variables with custom regular expressions e.g. {id:\d+}
        $route = preg_replace('/\{([a-z]+):([^\}]+)\}/', '(?P<\1>\2)', $route);

        // Add start and end delimiters, and case insensitive flag
        $route = '/^' . $route . '$/i';

        $this->routes[$route] = $params;
    }

    /**
     * Get all the routes from the routing table
     *
     * @return array
     */
    public function getRoutes()
    {
        return $this->routes;
    }

    /**
     * Dispatch the route, creating the controller object and calling the
     * action method
     *
     * @param Request $request Request object
     * @return void
     */
    public function dispatch(Request $request)
    {
        $this->request = $request;

        if ($this->match($this->request->uri())) {

            $controller = $this->request->params['controller'];
            $controller = $this->convertToStudlyCaps($controller);
            $controller = $this->getNamespace() . $controller;

            if (class_exists($controller)) {
                
                $controller_object = new $controller($this->request);

                $action = $this->request->params['action'];
                $action = $this->convertToCamelCase($action);

                if (is_callable([$controller_object, $action])) {

                    $controller_object->$action();

                } else {
                    throw new \Exception("Method $action (in controller $controller) not found");
                }

            } else {
                throw new \Exception("Controller class $controller not found");
            }

        } else {
            throw new \Exception('No route matched.', 404);
        }
    }

    /**
     * Match the route to the routes in the routing table, setting the $params
     * property if a route is found.
     *
     * @param string $url The route URL
     * @return boolean true if a match found, false otherwise
     */
    private function match($url)
    {
        foreach ($this->routes as $route => $params) {

            if (preg_match($route, $url, $matches)) {

                // Get named capture group values
                foreach ($matches as $key => $match) {

                    if (is_string($key)) {
                        $params[$key] = $match;
                    }
                }

                $this->request->params = $params;
                return true;
            }
        }

        return false;
    }

    /**
     * Convert the string with hyphens to StudlyCaps,
     * e.g. post-authors => PostAuthors
     *
     * @param string $string The string to convert
     * @return string String in studly caps
     */
    protected function convertToStudlyCaps($string)
    {
        return str_replace(' ', '', ucwords(str_replace('-', ' ', $string)));
    }

    /**
     * Convert the string with hyphens to camelCase,
     * e.g. add-new => addNew
     *
     * @param string $string The string to convert
     * @return string String in camel case
     */
    protected function convertToCamelCase($string)
    {
        return lcfirst($this->convertToStudlyCaps($string));
    }

    /**
     * Get the namespace for the controller class. The namespace defined in the
     * route parameters is added if present.
     * 
     * @return string The request URL
     */
    protected function getNamespace()
    {
        $namespace = 'Controllers\\';

        if (array_key_exists('namespace', $this->request->params)) {
            $namespace .= $this->params['namespace'] . '\\';
        }

        return $namespace;
    }
}
