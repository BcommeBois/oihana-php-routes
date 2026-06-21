<?php

namespace oihana\routes\http;

use oihana\enums\http\HttpMethod;

/**
 * Represents a route that registers an HTTP POST verb.
 *
 * This class extends `MethodRoute` and implements the `registerRoute`
 * template method to call the Slim App's `post()` method.
 *
 * By convention, it sets the default controller method to 'post'.
 *
 * @author  Marc Alcaraz (ekameleon)
 * @since   1.0.0
 * @package oihana\routes\http
 */
class PostRoute extends HttpMethodRoute
{
    /**
     * By convention, a POST route calls the 'post' method
     * on the controller, unless specified otherwise in $init.
     */
    const string INTERNAL_METHOD = HttpMethod::post ;

    /**
     * Implements the template method to register the route with the HTTP POST verb.
     *
     * @param callable $handler The handler (e.g., [$controller, 'post'])
     */
    protected function registerRoute( callable $handler ):void
    {
        $this->app->post( $this->getRoute() , $handler )->setName( $this->getName() )  ;
    }
}