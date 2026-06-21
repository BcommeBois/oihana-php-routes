<?php

namespace oihana\routes\http;

use oihana\enums\http\HttpMethod;

/**
 * Represents a route that registers an HTTP PATCH verb.
 *
 * This class extends `MethodRoute` and implements the `registerRoute`
 * template method to call the Slim App's `patch()` method.
 *
 * By convention, it sets the default controller method to 'patch'.
 *
 * @author  Marc Alcaraz (ekameleon)
 * @since   1.0.0
 * @package oihana\routes\http
 */
class PatchRoute extends HttpMethodRoute
{
    /**
     * By convention, a PATCH route calls the 'patch' method
     * on the controller, unless specified otherwise in $init.
     */
    const string INTERNAL_METHOD = HttpMethod::patch ;

    /**
     * Implements the template method to register the route with the HTTP DELETE verb.
     *
     * @param callable $handler The handler (e.g., [$controller, 'patch'])
     */
    protected function registerRoute( callable $handler ):void
    {
        $this->app->patch( $this->getRoute() , $handler )->setName( $this->getName() )  ;
    }
}