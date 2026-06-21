<?php

namespace oihana\routes\http;

use oihana\enums\http\HttpMethod;

/**
 * Represents a route that registers an HTTP GET verb,
 * dispatching to the controller's `list()` method by convention.
 *
 * This class extends `GetRoute` and only overrides the default
 * controller method name (`INTERNAL_METHOD`) to `'list'`.
 *
 * @author  Marc Alcaraz (ekameleon)
 * @since   1.0.0
 * @package oihana\routes\http
 */
class ListRoute extends GetRoute
{
    /**
     * By convention, a LIST route calls the 'list' method on the controller,
     * unless specified otherwise in $init.
     */
    public const string INTERNAL_METHOD = HttpMethod::list ;
}