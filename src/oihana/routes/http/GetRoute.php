<?php

namespace oihana\routes\http;

use oihana\enums\http\HttpMethod;

/**
 * Represents a route that registers an HTTP GET verb.
 *
 * This class extends `MethodRoute` and implements the `registerRoute`
 * template method to call the Slim App's `get()` method.
 *
 * By convention, it sets the default controller method to 'get'.
 *
 * @author  Marc Alcaraz (ekameleon)
 * @since   1.0.0
 * @package oihana\routes\http
 */
class GetRoute extends HttpMethodRoute
{
    /**
     * By convention, a GET route calls the 'get' method
     * on the controller, unless specified otherwise in $init.
     */
    public const string INTERNAL_METHOD = HttpMethod::get ;

    /**
     * Implements the template method to register the route with the HTTP GET verb.
     *
     * @param callable $handler The handler (e.g., [$controller, 'get'])
     */
    protected function registerRoute( callable $handler ):void
    {
        $this->app->get( $this->getRoute() , $handler )->setName( $this->getName() )  ;
    }
}