# Helpers

Two free functions registered through composer `autoload.files`, both under the
`oihana\routes\helpers` namespace. They are global functions, not class methods:
import each one with a `use function` statement, e.g.
`use function oihana\routes\helpers\withPlaceholder;`. One produces a passthrough
PSR-15 request handler (`responsePassthrough()`), the other builds a
Slim-compatible route pattern by appending a placeholder segment
(`withPlaceholder()`).

```php
use function oihana\routes\helpers\responsePassthrough;
use function oihana\routes\helpers\withPlaceholder;
```

Because they ship as plain functions, you can call them anywhere — inside a
`Route` class, a middleware, or a standalone service — without extending a base
class.

## responsePassthrough()

```php
function responsePassthrough(): callable
```

Returns a PSR-15 compatible request handler that simply returns the response
unchanged. This is useful for routes that don't need to perform any processing,
such as `OPTIONS` requests that only need to return CORS headers, or any route
where the middleware stack already handles everything.

The returned callable has the signature
`fn( ServerRequestInterface $request, ResponseInterface $response ): ResponseInterface`
and always returns the exact same `$response` instance it was given, ignoring the
`$request`.

```php
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

use function oihana\routes\helpers\responsePassthrough;

$handler = responsePassthrough();

// The handler returns the response object unchanged.
$result = $handler( $request, $response );
$result === $response; // true

// Typical usage on a Slim app:
$app->options( '/api/users', responsePassthrough() );  // CORS pre-flight
$app->head( '/api/users', responsePassthrough() );     // mirror GET
$app->get( '/health', responsePassthrough() );         // middleware does it all
```

## withPlaceholder()

```php
function withPlaceholder(
    string  $route,
    ?string $placeholder  = null,
    bool    $optional     = false,
    bool    $leadingSlash = true
): string
```

Builds a Slim-framework-compatible route by appending a placeholder segment to a
base path. It automatically handles trailing slashes on the base route, optional
segments (square brackets), and empty or `null` placeholders.

- `$route` — the base route path (e.g. `'/users'`).
- `$placeholder` — placeholder name, optionally with a regex constraint
  (e.g. `'id'`, `'id:[0-9]+'`, `'params:.*'`). It may already be wrapped in
  braces (`'{id}'`); the braces are not duplicated.
- `$optional` — when `true`, the placeholder is wrapped in square brackets to
  make the segment optional.
- `$leadingSlash` — when `true` (default), a leading slash is placed before the
  placeholder.

```php
use function oihana\routes\helpers\withPlaceholder;

// Required placeholder
withPlaceholder( '/users', 'id' );              // '/users/{id}'

// Required placeholder with a regex constraint
withPlaceholder( '/users', 'id:[0-9]+' );       // '/users/{id:[0-9]+}'

// Optional placeholder (wrapped in square brackets)
withPlaceholder( '/users', 'id', true );        // '/users[/{id}]'
withPlaceholder( '/users', 'id:[0-9]+', true ); // '/users[/{id:[0-9]+}]'

// Catch-all multi-segment placeholder
withPlaceholder( '/news', 'params:.*' );        // '/news/{params:.*}'
withPlaceholder( '/news', 'params:.*', true );  // '/news[/{params:.*}]'

// No leading slash (advanced: glue directly to the base)
withPlaceholder( '/users', 'id', false, false ); // '/users{id}'
```

### Edge cases

```php
use function oihana\routes\helpers\withPlaceholder;

// Already wrapped in braces — not duplicated
withPlaceholder( '/users', '{id}' );               // '/users/{id}'
withPlaceholder( '/users', '{id:[0-9]+}', true );  // '/users[/{id:[0-9]+}]'

// Empty placeholder — the base route is returned unchanged
withPlaceholder( '/users', '' );                   // '/users'

// Null (or omitted) placeholder — the base route is returned unchanged
withPlaceholder( '/users' );                       // '/users'

// Trailing slash on the base route is not duplicated
withPlaceholder( '/path/', 'id' );                 // '/path/{id}'
```

## See also

- [Routes](routes.md) — the `Route` base, its lifecycle and the registration traits.
- [HTTP method routes](http-routes.md) — the per-verb route classes built on `HttpMethodRoute`.
- [Documentation index](README.md) — back to the table of contents.
