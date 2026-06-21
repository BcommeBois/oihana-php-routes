<?php

namespace oihana\routes\http;

use oihana\enums\http\HttpMethod;

/**
 * Represents a route that registers an HTTP DELETE verb.
 *
 * This class extends `MethodRoute` and implements the `registerRoute`
 * template method to call the Slim App's `delete()` method.
 *
 * By convention, it sets the default controller method to 'delete'.
 *
 * @author  Marc Alcaraz (ekameleon)
 * @since   1.0.0
 * @package oihana\routes\http
 */
class DeleteRoute extends HttpMethodRoute
{
    /**
     * By convention, a DELETE route calls the 'delete' method
     * on the controller, unless specified otherwise in $init.
     */
    public const string INTERNAL_METHOD = HttpMethod::delete ;

    /**
     * Implements the template method to register the route with the HTTP DELETE verb.
     *
     * @param callable $handler The handler (e.g., [$controller, 'delete'])
     */
    protected function registerRoute( callable $handler ):void
    {
        $this->app->delete( $this->getRoute() , $handler )->setName( $this->getName() )  ;
    }
}