# oihana/php-routes — declarative HTTP routing for PHP

![Language](https://img.shields.io/badge/language-English-blue)

`oihana/php-routes` is a PHP 8.4+ library providing a small, declarative layer for defining HTTP routes on top of [Slim](https://www.slimframework.com/): a composable `Route` base, one class per HTTP verb, document and i18n routes, and helpers to register them on a Slim app from a PSR-11 container.

![Oihana PHP Routes](https://raw.githubusercontent.com/BcommeBois/oihana-php-routes/main/assets/images/oihana-php-routes-logo-inline-512x160.png)

## Who this documentation is for

PHP developers who want to:

- declare HTTP routes as small, **composable objects** rather than inline closures;
- map a URL pattern to a **controller method** per HTTP verb (`GetRoute`, `PostRoute`, …);
- build **document** and **localized (i18n)** routes with shared conventions;
- register everything on a Slim app wired through a **PSR-11 container**.

## Quick start

```php
use DI\Container;
use Slim\Factory\AppFactory;
use Slim\App;

use oihana\routes\http\GetRoute;

$container = new Container();
AppFactory::setContainer( $container );
$app = AppFactory::create();
$container->set( App::class , $app );

// Register: GET /api/test  ->  $controller->get()
$route = new GetRoute( $container , [
    'controllerID' => 'my.controller',
    'route'        => 'api/test',
] );

$route(); // registers the route on the Slim app
```

For full details, see the table of contents below.

## Table of contents

### Getting started — [`getting-started/`](getting-started/)

- [Introduction](getting-started/introduction.md) — what the library does and the *oihana* philosophy.
- [Installation](getting-started/installation.md) — PHP 8.4+ requirement and `composer require`.
- [Dependencies](getting-started/dependencies.md) — the runtime packages and their role.

### Usage

- [Routes](routes.md) — the `Route` base, its lifecycle and the registration traits.
- [HTTP method routes](http-routes.md) — the per-verb route classes built on `HttpMethodRoute`.
- [Document & i18n routes](document-i18n-routes.md) — `DocumentRoute` and `I18nRoute`.
- [Helpers](helpers.md) — the autoloaded free functions.
- [Flags](flags.md) — the `RouteFlag` bit flags.

### Cross-cutting

- [Tests & coverage](testing.md) — run the PHPUnit suite and measure coverage.

## Source code

The library code lives under [`src/oihana/routes/`](../../src/oihana/routes/) — namespace `oihana\routes`.

## See also

- [Packagist `oihana/php-routes`](https://packagist.org/packages/oihana/php-routes) — the package page.
- [API reference (phpDocumentor)](https://bcommebois.github.io/oihana-php-routes) — class-level generated reference.
