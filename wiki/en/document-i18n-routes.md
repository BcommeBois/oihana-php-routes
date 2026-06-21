# Document & i18n routes

![Language](https://img.shields.io/badge/language-English-blue)

The per-verb classes ([HTTP method routes](http-routes.md)) register **one** route each. Most resources, however, need a *family* of endpoints that share the same controller and URL conventions: list, count, create, read, update and delete a document; or expose a single localized property under several languages.

`oihana/php-routes` provides two higher-level `Route` subclasses for exactly these cases:

- `DocumentRoute` — registers a full CRUD route set for a document resource in a single `__invoke()`.
- `I18nRoute` — extends `DocumentRoute` to register localized (i18n) routes for one property of a document.

Both reuse the same `init` keys as the base [`Route`](routes.md) and the per-verb classes, so you never hardcode magic strings: the keys live in `oihana\routes\Route` (`Route::CONTROLLER_ID === 'controllerID'`, `Route::ROUTE === 'route'`, `Route::PROPERTY === 'property'`, …).

## DocumentRoute

`oihana\routes\DocumentRoute` extends `Route` and composes `HttpMethodRoutesTrait`. Where a `GetRoute` or `PostRoute` registers a single endpoint, a `DocumentRoute` registers the whole CRUD surface for a resource in one call, applying two placeholder conventions:

- **Collection endpoints** live on the bare route — `LIST`, `COUNT`, `POST` and a matching `OPTIONS` on `/route`.
- **Document endpoints** live on the route plus an id placeholder — `GET`, `PATCH`, `PUT` and `DELETE` on `/route/{id:[0-9]+}`, with their own `OPTIONS`. The id placeholder comes from the `routePattern` key (default `id:[0-9]+`) and is appended by the `withPlaceholder()` [helper](helpers.md).

When `hasDeleteMultiple()` is enabled, the `DELETE` route is registered with an **optional** id segment — `/route[/{id:[0-9]+}]` — so the same endpoint can delete one or many documents.

Each verb is only emitted when its flag is enabled (see [Flags](flags.md)); if no method flag is on, nothing is registered. If the `controllerID` is not present in the DI container, `__invoke()` logs a warning and registers nothing.

### `init` keys

`DocumentRoute` reads the base `Route` keys plus the flag/method keys handled by `HttpMethodRoutesTrait`:

| Key | Constant | Role |
|---|---|---|
| `controllerID` | `Route::CONTROLLER_ID` | Service id of the controller resolved from the container. |
| `route` | `Route::ROUTE` | Base route path, e.g. `'users'` → `/users`. |
| `routePattern` | `Route::ROUTE_PLACEHOLDER` | Id placeholder appended to document endpoints (default `id:[0-9]+`). |
| `flags` | `Route::FLAGS` | `RouteFlag` bit flags selecting which verbs are enabled. |
| `name` / `prefix` / `suffix` | `Route::NAME` / `Route::PREFIX` / `Route::SUFFIX` | Optional route-name composition. |

The constructor calls `initializeFlags()` then `initializeMethods()`, so you can pass `flags` as a `RouteFlag` value and, optionally, per-verb controller-method overrides via the `HttpMethod` keys.

### Example

Grounded in `DocumentRouteTest`: a single anonymous controller exposes the CRUD methods, and `RouteFlag::DEFAULT` enables the standard verbs.

```php
use DI\Container;
use Slim\App;
use Slim\Factory\AppFactory;

use oihana\routes\DocumentRoute;
use oihana\routes\Route;
use oihana\routes\enums\RouteFlag;

$container = new Container();
AppFactory::setContainer( $container );
$app = AppFactory::create();
$container->set( App::class , $app );

// The controller exposing one method per verb.
$container->set( 'my.controller' , new class
{
    public function get()    : string { return 'get'    ; }
    public function list()   : string { return 'list'   ; }
    public function count()  : string { return 'count'  ; }
    public function post()   : string { return 'post'   ; }
    public function patch()  : string { return 'patch'  ; }
    public function put()    : string { return 'put'    ; }
    public function delete() : string { return 'delete' ; }
} );

$route = new DocumentRoute( $container ,
[
    Route::CONTROLLER_ID => 'my.controller' ,
    Route::ROUTE         => 'users' ,
    Route::FLAGS         => RouteFlag::DEFAULT ,
]);

$route(); // registers the full CRUD route set on the Slim app

// /users            -> LIST / COUNT / POST / OPTIONS
// /users/{id:[0-9]+} -> GET / PATCH / PUT / DELETE / OPTIONS
```

With `RouteFlag::NONE`, no verb flag is enabled and `__invoke()` registers nothing.

## I18nRoute

`oihana\routes\I18nRoute` extends `DocumentRoute` and is meant for **localized** access to a single property of a document. Instead of a full CRUD surface, it registers a focused route set under one property segment:

```
/route/{id:[0-9]+}/property
```

The path is built from `getRoute()`, the id placeholder (`routePattern`, default `id:[0-9]+`) applied by `withPlaceholder()`, and the `property` key. On that path it registers:

- an `OPTIONS` route;
- a `GET` route bound to the controller method named after the `property`;
- a `PATCH` route bound to `patch` + ucfirst(property) (for example, property `title` → controller method `patchTitle`).

This lets a localization layer read and update one field (a translatable `title`, `description`, …) per document, while the language itself is typically resolved from the request (headers, query, or a path segment of your routing setup). As with `DocumentRoute`, a missing `controllerID` in the container makes `__invoke()` log a warning and register nothing.

### `init` keys

| Key | Constant | Role |
|---|---|---|
| `controllerID` | `Route::CONTROLLER_ID` | Service id of the controller resolved from the container. |
| `route` | `Route::ROUTE` | Base route path, e.g. `'articles'` → `/articles`. |
| `property` | `Route::PROPERTY` | The localized property segment and method base name, e.g. `'title'`. |
| `routePattern` | `Route::ROUTE_PLACEHOLDER` | Id placeholder (default `id:[0-9]+`). |

### Example

Grounded in `I18nRouteTest`: the controller exposes `get()` and `patch()`, and the route targets the `title` property.

```php
use DI\Container;
use Slim\App;
use Slim\Factory\AppFactory;

use oihana\routes\I18nRoute;
use oihana\routes\Route;

$container = new Container();
AppFactory::setContainer( $container );
$app = AppFactory::create();
$container->set( App::class , $app );

$container->set( 'my.controller' , new class
{
    public function get()   : string { return 'get'   ; }
    public function patch() : string { return 'patch' ; }
} );

$route = new I18nRoute( $container ,
[
    Route::CONTROLLER_ID => 'my.controller' ,
    Route::ROUTE         => 'articles' ,
    Route::PROPERTY      => 'title' ,
]);

$route(); // registers the localized property routes on the Slim app

// /articles/{id:[0-9]+}/title -> OPTIONS / GET / PATCH
```

## See also

- [Routes](routes.md) — the `Route` base, its lifecycle and the registration traits.
- [HTTP method routes](http-routes.md) — the per-verb route classes built on `HttpMethodRoute`.
- [Helpers](helpers.md) — the autoloaded free functions, including `withPlaceholder()`.
- [Documentation index](README.md) — back to the table of contents.
