<?php

namespace oihana\routes\http;

use oihana\enums\http\HttpMethod;

/**
 * Represents a route that registers an HTTP PUT verb.
 *
 * This class extends `MethodRoute` and implements the `registerRoute`
 * template method to call the Slim App's `put()` method.
 *
 * By convention, it sets the default controller method to 'put'.
 *
 * @author  Marc Alcaraz (ekameleon)
 * @since   1.0.0
 * @package oihana\routes\http
 */
class PutRoute extends HttpMethodRoute
{
    /**
     * By convention, a PUT route calls the 'put' method
     * on the controller, unless specified otherwise in $init.
     */
    const string INTERNAL_METHOD = HttpMethod::put ;

    /**
     * Implements the template method to register the route with the HTTP PUT verb.
     *
     * @param callable $handler The handler (e.g., [$controller, 'put'])
     */
    protected function registerRoute( callable $handler ):void
    {
        $this->app->put( $this->getRoute() , $handler )->setName( $this->getName() )  ;
    }
}