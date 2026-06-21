# Flags

`oihana\routes\enums\RouteFlag` is the catalogue of **bit-flag constants** that
control which HTTP routes a route object enables (GET, POST, DELETE, …). It is
**not** a native PHP `enum`: like the other *oihana* enumerations it is a plain
class pulling in `oihana\reflect\traits\ConstantsTrait`, so its values stay
ordinary `int`s you can combine and store anywhere, while remaining
introspectable.

Each flag is a distinct power-of-two bit (`1 << 0`, `1 << 1`, …), so several
flags fit in a single integer **bitmask**. You combine them with the bitwise OR
operator `|`, and you test a mask for a given flag with
`oihana\core\bits\hasFlag()`:

```php
use oihana\routes\enums\RouteFlag;
use function oihana\core\bits\hasFlag;

// Combine two flags into one bitmask:
$mask = RouteFlag::GET | RouteFlag::POST ;

// Test the mask:
hasFlag( $mask , RouteFlag::GET );   // true
hasFlag( $mask , RouteFlag::PATCH ); // false
```

Because it composes `ConstantsTrait`, `RouteFlag` also exposes a small
reflection API (`RouteFlag::enums()`, `RouteFlag::getConstants()`, …), plus a
few dedicated helpers: `RouteFlag::describe()` (human-readable label),
`RouteFlag::getFlags()` (decompose a mask into its individual flags),
`RouteFlag::has()` (same test as `hasFlag()`), `RouteFlag::isValid()`
(reject unknown bits) and `RouteFlag::convertLegacyArray()` (turn the legacy
`hasGet`/`hasPost`/… boolean array into a mask).

## Constants

### Individual route flags

Each constant below enables a single route. They are the building blocks you
combine with `|`.

| Constant | Value | Meaning |
|---|---|---|
| `RouteFlag::NONE` | `0` | No routes enabled. |
| `RouteFlag::COUNT` | `1` (`1 << 0`) | Enable the COUNT route. |
| `RouteFlag::DELETE` | `2` (`1 << 1`) | Enable the DELETE route. |
| `RouteFlag::DELETE_MULTIPLE` | `4` (`1 << 2`) | Enable DELETE with multiple IDs support. |
| `RouteFlag::GET` | `8` (`1 << 3`) | Enable the GET route. |
| `RouteFlag::LIST` | `16` (`1 << 4`) | Enable the LIST route. |
| `RouteFlag::PATCH` | `32` (`1 << 5`) | Enable the PATCH route. |
| `RouteFlag::POST` | `64` (`1 << 6`) | Enable the POST route. |
| `RouteFlag::PUT` | `128` (`1 << 7`) | Enable the PUT route. |

### Composite presets

These constants are pre-combined masks for the most common policies.

| Constant | Value | Meaning |
|---|---|---|
| `RouteFlag::ALL` | `255` | All valid flags combined (used for validation by `isValid()`). |
| `RouteFlag::DEFAULT` | `255` | Default routes: every CRUD operation enabled (`COUNT \| DELETE \| DELETE_MULTIPLE \| GET \| LIST \| PATCH \| POST \| PUT`). |
| `RouteFlag::READ_ONLY` | `25` | Read-only routes: `GET \| LIST \| COUNT`. |
| `RouteFlag::WRITE` | `230` | Write routes: `POST \| PUT \| PATCH \| DELETE \| DELETE_MULTIPLE`. |
| `RouteFlag::CRUD` | `218` | Basic CRUD without count: `GET \| LIST \| POST \| PUT \| DELETE`. |

### Reflection / legacy constants

`RouteFlag` also declares a few `string` and `array` constants used internally.

| Constant | Value | Meaning |
|---|---|---|
| `RouteFlag::DEFAULT_FLAG` | `'defaultFlag'` | Legacy array key controlling the default state when converting via `convertLegacyArray()`. |
| `RouteFlag::HAS_COUNT` | `'hasCount'` | Legacy boolean key mapped to `COUNT`. |
| `RouteFlag::HAS_DELETE` | `'hasDelete'` | Legacy boolean key mapped to `DELETE`. |
| `RouteFlag::HAS_DELETE_MULTIPLE` | `'hasDeleteMultiple'` | Legacy boolean key mapped to `DELETE_MULTIPLE`. |
| `RouteFlag::HAS_GET` | `'hasGet'` | Legacy boolean key mapped to `GET`. |
| `RouteFlag::HAS_LIST` | `'hasList'` | Legacy boolean key mapped to `LIST`. |
| `RouteFlag::HAS_PATCH` | `'hasPatch'` | Legacy boolean key mapped to `PATCH`. |
| `RouteFlag::HAS_POST` | `'hasPost'` | Legacy boolean key mapped to `POST`. |
| `RouteFlag::HAS_PUT` | `'hasPut'` | Legacy boolean key mapped to `PUT`. |
| `RouteFlag::FLAGS` | `[…]` | The ordered list of individual flag values (used by `getFlags()`). |
| `RouteFlag::FLAGS_NAME` | `[…]` | Map of flag value → name (used by `describe()`). |

## Example

Combining flags with `|` and letting a route read them through
`oihana\routes\traits\HasRouteTrait` (the trait every HTTP route uses to expose
`hasGet()`, `hasPost()`, … from its `flags` bitmask):

```php
<?php

require 'vendor/autoload.php';

use oihana\routes\enums\RouteFlag;
use oihana\routes\traits\HasRouteTrait;

use function oihana\core\bits\hasFlag;

// A tiny object that consumes the flags through the shared trait.
class FakeRoute
{
    use HasRouteTrait ;
}

$route = new FakeRoute() ;

// Compose a read-only mask plus the ability to create new documents:
$route->initializeFlags( RouteFlag::READ_ONLY | RouteFlag::POST ) ;

// The trait reads the bitmask with hasFlag() under the hood:
var_dump( $route->hasGet() );    // bool(true)   — GET is in READ_ONLY
var_dump( $route->hasList() );   // bool(true)   — LIST is in READ_ONLY
var_dump( $route->hasPost() );   // bool(true)   — added explicitly
var_dump( $route->hasDelete() ); // bool(false)  — never enabled

// You can test the raw mask yourself the same way the trait does:
var_dump( hasFlag( $route->flags , RouteFlag::COUNT ) ); // bool(true)

// Human-readable label of everything enabled:
echo $route->describeFlags() , PHP_EOL ; // COUNT, GET, LIST, POST

// Toggle flags at runtime:
$route->enableFlags( RouteFlag::DELETE ) ;  // turn DELETE on
$route->disableFlags( RouteFlag::COUNT ) ;  // turn COUNT off
echo RouteFlag::describe( $route->flags ) , PHP_EOL ; // DELETE, GET, LIST, POST
```

When an HTTP route is registered, the same `hasGet()`/`hasPost()`/… predicates
gate which endpoints are actually wired on the Slim app — see
[HTTP method routes](http-routes.md).

## See also

- [Routes](routes.md) — the `Route` base, its lifecycle and the registration traits.
- [HTTP method routes](http-routes.md) — the per-verb route classes that read these flags.
- [Documentation index](README.md) — back to the table of contents.
