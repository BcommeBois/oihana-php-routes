# Routes

`oihana\routes\Route` is the composable base class every route definition in
`oihana/php-routes` is built on. It is wired to a
[PSR-11](https://www.php-fig.org/psr/psr-11/) container (PHP-DI's `DI\Container`):
the constructor receives the container and stores it on the public `$container`
property, then reads an optional `$init` array to configure the route.

A `Route` maps a **URL pattern** to a **controller method** and knows how to
**register itself on the Slim app** resolved from the container. Concrete verb
classes (`GetRoute`, `PostRoute`, …) extend `Route` through the intermediate
`HttpMethodRoute` base and implement the actual Slim registration; the base
`Route` provides the shared lifecycle (naming, route normalization, nested
route invocation) and the building blocks the verb traits rely on.

## The Route lifecycle

### Constructor and `init` keys

```php
public function __construct( DI\Container $container , array $init = [] )
```

The constructor calls `initializeApp()` and `initializeLogger()` (resolving the
Slim `App` and a PSR-3 logger from the container), then reads the following keys
from `$init`. Each key has a matching `Route::` constant so you can avoid inline
string literals:

| `init` key (constant) | String | Property | Default |
| --- | --- | --- | --- |
| `Route::CONTROLLER_ID` | `'controllerID'` | `$controllerID` | `null` |
| `Route::NAME` | `'name'` | `$name` | `null` |
| `Route::OWNER_PLACEHOLDER` | `'ownerPlaceHolder'` | `$ownerPlaceholder` | `Route::DEFAULT_OWNER_PLACEHOLDER` (`'owner:[0-9]+'`) |
| `Route::PREFIX` | `'prefix'` | `$prefix` | `Route::DEFAULT_PREFIX` (`'api'`) |
| `Route::PROPERTY` | `'property'` | `$property` | `''` |
| `Route::ROUTE` | `'route'` | `$route` | `null` |
| `Route::ROUTE_PLACEHOLDER` | `'routePattern'` | `$routePlaceholder` | `Route::DEFAULT_ROUTE_PLACEHOLDER` (`'id:[0-9]+'`) |
| `Route::ROUTES` | `'routes'` | `$routes` | `null` |
| `Route::SUFFIX` | `'suffix'` | `$suffix` | `''` |

```php
use DI\Container;
use Slim\App;
use Slim\Factory\AppFactory;

use oihana\routes\Route;

$container = new Container() ;
AppFactory::setContainer( $container ) ;
$container->set( App::class , AppFactory::create() ) ;

$route = new Route( $container , [
    Route::CONTROLLER_ID => 'my.controller' ,
    Route::NAME          => 'foo' ,
    Route::PREFIX        => 'bar' ,
    Route::SUFFIX        => 'baz' ,
    Route::ROUTE         => '/api/test' ,
] ) ;

echo $route->name ;   // "foo"
echo $route->prefix ; // "bar"
echo $route->route ;  // "/api/test"
```

When no `init` is provided the defaults apply:

```php
$route = new Route( $container ) ;

echo $route->prefix ;           // "api"
echo $route->ownerPlaceholder ; // "owner:[0-9]+"
echo $route->routePlaceholder ; // "id:[0-9]+"
```

### `getRoute()` — the normalized path

```php
public function getRoute() : string
```

Returns the main route, always prefixed with a single leading `/` (any leading
slash on the input is collapsed). When `$route` is `null` it returns `"/"`.

```php
$a = new Route( $container , [ Route::ROUTE => 'foo/bar' ] ) ;
echo $a->getRoute() ; // "/foo/bar"

$b = new Route( $container , [ Route::ROUTE => '/foo/bar' ] ) ;
echo $b->getRoute() ; // "/foo/bar"
```

### `dotify()` — slashes to dots

```php
public function dotify( string $route ) : string
```

Converts a `'foo/bar'` path into the `'foo.bar'` form used for route names.
A string without a slash is returned unchanged.

```php
$route = new Route( $container ) ;
echo $route->dotify( 'foo/bar' ) ; // "foo.bar"
echo $route->dotify( 'foobar' ) ;  // "foobar"
```

### `getName()` — the qualified route name

```php
public function getName() : string
```

Builds the fully qualified, dot-separated route name from `$prefix`, the
explicit `$name` (or the dotified route when no name is given) and `$suffix`.
Empty segments are trimmed away.

```php
$named = new Route( $container , [
    Route::PREFIX => 'api' ,
    Route::NAME   => 'user.get' ,
    Route::SUFFIX => 'json' ,
] ) ;
echo $named->getName() ; // "api.user.get.json"

$fromRoute = new Route( $container , [ Route::ROUTE => 'foo/bar' ] ) ;
echo $fromRoute->getName() ; // "api.foo.bar"
```

### `create()` — build a child Route from a definition

```php
public function create( array|Route|null $definition ) : ?Route
```

Normalizes a route definition into a `Route` instance:

- a `Route` object is returned as-is;
- an **associative** array is turned into a new route of class `$definition['clazz']`
  (defaulting to `GetRoute::class`), inheriting `controllerID` and `route` from
  the parent when the definition omits them;
- anything else (a non-associative array, `null`) returns `null`.

```php
$parent = new Route( $container ) ;

$child = $parent->create( [
    Route::CLAZZ => Route::class ,
    Route::NAME  => 'foo' ,
    Route::ROUTE => '/test' ,
] ) ;

echo $child->name ;  // "foo"
echo $child->route ; // "/test"

$parent->create( null ) ;                // null
$parent->create( [ 'not_associative' ] ) ; // null
```

### `__invoke()` — register the nested routes

```php
public function __invoke() : void
```

When `$routes` holds one or more nested definitions, each is passed through
`create()` and then invoked, so calling the parent registers the whole subtree
on the Slim app. On the base `Route` it is a no-op when `$routes` is empty;
verb subclasses override `__invoke()` to perform the actual Slim registration.

```php
$child = new class( $container ) extends Route
{
    public bool $invoked = false ;
    public function __invoke() : void { $this->invoked = true ; }
} ;

$parent = new Route( $container , [ Route::ROUTES => [ $child ] ] ) ;
$parent() ; // runs every nested route

var_dump( $child->invoked ) ; // bool(true)
```

### `execute()` — run a callable or a list of callables

```php
public function execute( mixed $routes ) : void
```

A small helper that invokes a single callable, or iterates and invokes every
callable in an array (non-callable entries are skipped).

```php
$route  = new Route( $container ) ;
$called = 0 ;

$route->execute( fn() => $called++ ) ;          // single callable
$route->execute( [ fn() => $called++ , fn() => $called++ ] ) ; // array

echo $called ; // 3
```

## Registration traits

The verb route classes and the document/i18n routes are assembled from two
traits that the base `Route` does not use directly but that operate on its
properties (`$container`, `$controllerID`).

### `HasRouteTrait`

Carries the route bitmask and the predicates that decide which sub-routes are
enabled. It exposes:

- `public int $flags` — the enabled-route bitmask (default `RouteFlag::DEFAULT`).
- `initializeFlags( array|int $init = [] ) : static` — sets `$flags` from an
  integer, from the `Route::FLAGS` key of an array, or by converting a legacy
  associative array via `RouteFlag::convertLegacyArray()`.
- `hasCount()`, `hasDelete()`, `hasDeleteMultiple()`, `hasGet()`, `hasList()`,
  `hasPatch()`, `hasPost()`, `hasPut()` — `bool` predicates checking a flag.
- `enableFlags( int $flags ) : static` / `disableFlags( int $flags ) : static` —
  turn flags on or off.
- `describeFlags() : string` — a human-readable description of the enabled routes.

```php
use oihana\routes\enums\RouteFlag;
use oihana\routes\traits\HasRouteTrait;

$host = new class { use HasRouteTrait ; } ;

$host->initializeFlags( RouteFlag::GET | RouteFlag::LIST ) ;
var_dump( $host->hasGet() ) ;    // bool(true)
var_dump( $host->hasDelete() ) ; // bool(false)

$host->enableFlags( RouteFlag::DELETE ) ;
var_dump( $host->hasDelete() ) ; // bool(true)
```

### `HttpMethodRoutesTrait`

Builds on `HasRouteTrait` and generates per-verb `Route` instances, appending
them to a `&$routes` array (only when the matching flag is enabled). It holds
overridable controller-method names and the generators:

- `public ?string $delete , $get , $list , $patch , $post , $put` — the
  controller method to call per verb.
- `initializeMethods( array $init = [] ) : static` — fills those names from the
  `HttpMethod::delete` / `get` / `list` / `patch` / `post` / `put` keys.
- `count()`, `delete()`, `get()`, `list()`, `patch()`, `post()`, `put()` — each
  appends the right route class (`GetRoute`, `DeleteRoute`, …) to `&$routes`
  when its flag is set.
- `options( array &$routes , string $route , bool $flag = true ) : void` —
  appends an `OptionsRoute` (regardless of flags) unless `$flag` is `false`.
- `method( string $clazz , array &$routes , string $route , ?string $method = null ) : void` —
  the low-level builder; throws `InvalidArgumentException` when `$clazz` is not a
  subclass of `Route`.

```php
use DI\Container;
use Slim\App;
use Slim\Factory\AppFactory;

use oihana\routes\enums\RouteFlag;
use oihana\routes\http\GetRoute;
use oihana\routes\traits\HttpMethodRoutesTrait;

$container = new Container() ;
AppFactory::setContainer( $container ) ;
$container->set( App::class , AppFactory::create() ) ;

$host = new class
{
    use HttpMethodRoutesTrait ;
    public Container $container ;
    public ?string $controllerID = 'my.controller' ;
} ;

$host->container = $container ;
$host->initializeFlags( RouteFlag::DEFAULT ) ;
$host->get = 'fetchAll' ;

$routes = [] ;
$host->get( $routes , '/users' ) ;

echo get_class( $routes[0] ) ; // "...\GetRoute"
echo $routes[0]->method ;      // "fetchAll"
```

## See also

- [HTTP method routes](http-routes.md) — the per-verb route classes built on `HttpMethodRoute`.
- [Document & i18n routes](document-i18n-routes.md) — `DocumentRoute` and `I18nRoute`.
- [Flags](flags.md) — the `RouteFlag` bit flags.
- [Documentation index](README.md) — full table of contents.
