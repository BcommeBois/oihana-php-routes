<?php

namespace oihana\routes\helpers ;

use oihana\enums\Char;

use function oihana\core\strings\betweenBraces;
use function oihana\core\strings\betweenBrackets;

/**
 * Builds a Slim-framework-compatible route by appending a placeholder.
 *
 * This helper function assembles a base route and a placeholder segment,
 * automatically handling trailing slashes, optional segments, and empty
 * placeholders. It is designed to simplify the creation of dynamic
 * route patterns.
 *
 * @param string  $route        The base route path (e.g., '/users').
 * @param ?string $placeholder  Placeholder name, optionally with regex (e.g., 'id', 'id:[0-9]+', 'params:.*')
 * @param bool    $optional     If `true`, wrap the placeholder in square brackets
 * @param bool    $leadingSlash If `true`, a leading slash `/` will be prepended to the placeholder (Default true).
 *
 * @return string Slim route with placeholder
 *
 * @example
 * ```php
 * // Placeholder requis simple
 * withPlaceholder('/users', 'id');
 * // -> '/users/{id}'
 *
 * // Placeholder requis avec une contrainte regex
 * withPlaceholder('/articles', 'slug:[a-z0-9-]+');
 * // -> '/articles/{slug:[a-z0-9-]+}'
 *
 * // Placeholder optionnel
 * withPlaceholder('/search', 'query', true);
 * // -> '/search[/{query}]'
 *
 * // Placeholder "catch-all" optionnel pour capturer plusieurs segments
 * withPlaceholder('/files', 'path:.*', true);
 * // -> '/files[/{path:.*}]'
 *
 * // Gère correctement le slash final sur la route de base
 * withPlaceholder('/products/', 'sku');
 * // -> '/products/{sku}'
 *
 * // Cas d'un placeholder vide ou null
 * withPlaceholder('/home', null);
 * // -> '/home'
 *
 * // Sans ajout de slash initial (cas d'usage avancé)
 * withPlaceholder('/user-', 'id', false, false);
 * // -> '/user-{id}'
 * ```
 *
 */
function withPlaceholder
(
    string  $route ,
    ?string $placeholder  = null ,
    bool    $optional     = false ,
    bool    $leadingSlash = true
)
: string
{
    $placeholder = trim( (string) $placeholder );

    if ( empty( $placeholder ) )
    {
        return $route;
    }

    $route   = rtrim ( $route , Char::SLASH ) ;
    $segment = betweenBraces( $placeholder ) ;

    if ( $optional )
    {
        $segment = betweenBrackets(( $leadingSlash ? Char::SLASH : Char::EMPTY ) . $segment ) ;
    }
    else if ( $leadingSlash )
    {
        $segment = Char::SLASH . $segment;
    }

    return $route . $segment ;
}