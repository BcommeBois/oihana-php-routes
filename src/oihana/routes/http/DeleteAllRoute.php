<?php

namespace oihana\routes\http;

use oihana\enums\http\HttpMethod;

/**
 * Represents a route that registers an HTTP DELETE verb,
 * dispatching to the controller's `deleteAll()` method by convention.
 *
 * This class extends `DeleteRoute` and only overrides the default
 * controller method name (`INTERNAL_METHOD`) to `'deleteAll'`.
 *
 * Typically used on a collection URL (e.g. `DELETE /users`) to remove
 * all resources, as opposed to `DeleteRoute` used on a single resource
 * (e.g. `DELETE /users/{id}`).
 *
 * @author  Marc Alcaraz (ekameleon)
 * @since   1.0.0
 * @package oihana\routes\http
 */
class DeleteAllRoute extends DeleteRoute
{
    /**
     * By convention, a DELETE ALL route calls the 'deleteAll' method
     * on the controller, unless specified otherwise in $init.
     */
    public const string INTERNAL_METHOD = HttpMethod::deleteAll ;
}
