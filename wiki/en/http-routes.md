# HTTP method routes

![Language](https://img.shields.io/badge/language-English-blue)

`HttpMethodRoute` is the base class for routes that map a single HTTP verb to a **controller method**.

Each subclass registers exactly one HTTP verb on the Slim app (`GetRoute` → `GET`, `PostRoute` → `POST`, …) and, by convention, dispatches to a same-named method on the controller resolved from the PSR-11 container. That convention is captured by the `INTERNAL_METHOD` constant: a `GetRoute` calls `$controller->get()`, a `DeleteRoute` calls `$controller->delete()`, and so on — unless you override it through the `method` init key.

## `HttpMethodRoute`

`HttpMethodRoute` extends [`Route`](routes.md) and adds a single `string $method` property plus the registration machinery. It is **abstract**: subclasses provide the verb-specific `registerRoute()` implementation.

### The `INTERNAL_METHOD` convention

Each subclass redefines the constant:

```php
public const string INTERNAL_METHOD = HttpMethod::get ;
```

At construction time, `initializeMethod()` resolves the effective method name:

```php
$this->method = $init[ static::METHOD ] ?? static::INTERNAL_METHOD ;
```

So the controller method is `INTERNAL_METHOD` by default, but the `method` init key (the `Route::METHOD` constant, value `'method'`) always wins when present.

### The `registerRoute()` template method

`__invoke()` is the entry point. It validates the wiring, then delegates the actual registration to the abstract `registerRoute()` template method:

1. it checks that `controllerID` is registered in the container — if not, it logs a warning and returns without registering anything;
2. it resolves the controller via `$this->container->get( $this->controllerID )`;
3. it checks that the controller exposes the resolved `$this->method` (via `method_exists`) — otherwise it logs a warning and returns;
4. finally it calls `registerRoute( [ $controller , $this->method ] )` with the handler as a `[$controller, 'method']` callable.

Each subclass implements `registerRoute()` by calling the matching Slim verb method and naming the route. For example, `GetRoute`:

```php
protected function registerRoute( callable $handler ):void
{
    $this->app->get( $this->getRoute() , $handler )->setName( $this->getName() ) ;
}
```

The route pattern comes from `getRoute()` and the route name from `getName()`, both inherited from [`Route`](routes.md).

### Runnable example

```php
use DI\Container;
use Slim\Factory\AppFactory;
use Slim\App;

use oihana\routes\http\GetRoute;

$container = new Container();
AppFactory::setContainer( $container );
$app = AppFactory::create();
$container->set( App::class , $app );

// A controller exposing a 'get' method.
$controller = new class
{
    public function get(): string { return 'get-called'; }
};
$container->set( 'my.controller' , $controller );

// Register: GET /api/test  ->  $controller->get()
$route = new GetRoute( $container , [
    'controllerID' => 'my.controller',
    'route'        => 'api/test',
] );

$route(); // resolves the controller and registers the route on the Slim app
```

## Per-verb classes

All classes live in the `oihana\routes\http` namespace.

| Class            | HTTP verb | Default controller method (`INTERNAL_METHOD`) |
| ---------------- | --------- | --------------------------------------------- |
| `GetRoute`       | `GET`     | `get`                                         |
| `PostRoute`      | `POST`    | `post`                                        |
| `PutRoute`       | `PUT`     | `put`                                         |
| `PatchRoute`     | `PATCH`   | `patch`                                       |
| `DeleteRoute`    | `DELETE`  | `delete`                                      |
| `DeleteAllRoute` | `DELETE`  | `deleteAll`                                   |
| `OptionsRoute`   | `OPTIONS` | — (see below)                                 |
| `ListRoute`      | `GET`     | `list`                                        |
| `SearchRoute`    | `GET`     | `search`                                      |

Notes:

- `DeleteAllRoute` extends `DeleteRoute` and only overrides `INTERNAL_METHOD` to `deleteAll`. It still registers a `DELETE` verb — typically on a collection URL (e.g. `DELETE /users` to remove all resources, versus `DELETE /users/{id}` for a single one).
- `ListRoute` extends `GetRoute` and only overrides `INTERNAL_METHOD` to `list`. It still registers a `GET` verb.
- `OptionsRoute` is special: it extends [`Route`](routes.md) directly rather than `HttpMethodRoute`. It does **not** dispatch to a controller method; instead it registers a Slim `options()` route using the `responsePassthrough` helper, which returns the response untouched (typically modified by CORS middleware for preflight requests).
- `SearchRoute` extends `GetRoute` and only overrides `INTERNAL_METHOD` to `search`. It still registers a `GET` verb — typically on a collection URL (e.g. `GET /users/search`) to run a server-side search, as opposed to `ListRoute` which lists the whole collection.

## Custom method override

The default convention can be overridden per route with the `method` init key. The handler still resolves from the same `controllerID`, but it targets the method you name:

```php
use oihana\routes\http\GetRoute;

// GET /foo  ->  $controller->fetch()   (instead of the default ->get())
$route = new GetRoute( $container , [
    'controllerID' => 'my.controller',
    'route'        => 'foo',
    'method'       => 'fetch',
] );

$route();
```

This works for every `HttpMethodRoute` subclass. For instance, a `DeleteAllRoute` with `'method' => 'truncate'` registers a `DELETE` verb that dispatches to `$controller->truncate()`.

## See also

- [Routes](routes.md) — the `Route` base, its lifecycle and the registration traits.
- [Document & i18n routes](document-i18n-routes.md) — `DocumentRoute` and `I18nRoute`.
- Back to the [Documentation index](README.md).
