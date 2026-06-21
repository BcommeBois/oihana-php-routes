<?php

namespace oihana\routes\http;

use oihana\enums\http\HttpMethod;

/**
 * Represents a route that registers an HTTP GET verb,
 * dispatching to the controller's `search()` method by convention.
 *
 * This class extends `GetRoute` and only overrides the default
 * controller method name (`INTERNAL_METHOD`) to `'search'`.
 *
 * Typically used on a collection URL (e.g. `GET /users/search`) to run a
 * server-side search, as opposed to `ListRoute` which lists the whole
 * collection.
 *
 * @author  Marc Alcaraz (ekameleon)
 * @since   1.0.0
 * @package oihana\routes\http
 */
class SearchRoute extends GetRoute
{
    /**
     * By convention, a SEARCH route calls the 'search' method on the controller,
     * unless specified otherwise in $init.
     */
    public const string INTERNAL_METHOD = HttpMethod::search ;
}
