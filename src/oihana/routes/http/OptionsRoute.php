<?php

namespace oihana\routes\http;

use oihana\routes\Route;

use function oihana\routes\helpers\responsePassthrough;

/**
 * Represents a route that registers an HTTP OPTIONS verb.
 *
 * This class is typically used for CORS preflight requests.
 *
 * It extends the base `Route` class and registers an `options` route
 * using the `responsePassthrough` helper, which simply returns the
 * response (often modified by middleware) without any further processing.
 *
 * @author  Marc Alcaraz (ekameleon)
 * @since   1.0.0
 * @package oihana\routes\http
 */
class OptionsRoute extends Route
{
    /**
     * Called when a script tries to call the instance as a function.
     */
    public function __invoke(): void
    {
        $this->app->options( $this->getRoute() , responsePassthrough(...) ) ;
    }
}