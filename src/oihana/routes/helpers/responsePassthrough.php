<?php

namespace oihana\routes\helpers ;

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

/**
 * Returns a passthrough handler that simply returns the response unchanged.
 *
 * This is useful for routes that don't need to perform any processing,
 * such as OPTIONS requests that only need to return CORS headers or
 * other middleware-handled responses.
 *
 * ## Usage Examples
 *
 * ```php
 * use function oihana\routes\helpers\responsePassthrough;
 *
 * // Simple OPTIONS route
 * $app->options('/api/users', responsePassthrough(...));
 *
 * // HEAD route that mirrors GET
 * $app->head('/api/users', responsePassthrough(...));
 *
 * // Any route where middleware handles everything
 * $app->get('/health', responsePassthrough(...));
 * ```
 *
 * @return callable A PSR-15 compatible request handler that returns the response as-is.
 *
 * @author  Marc Alcaraz (ekameleon)
 * @since   1.0.0
 * @package oihana\routes\helpers
 */
function responsePassthrough(): callable
{
    return fn( Request $request , Response $response ): Response => $response ;
}