# Oihana PHP - Routes

![Oihana PHP Routes](https://raw.githubusercontent.com/BcommeBois/oihana-php-routes/main/assets/images/oihana-php-routes-logo-inline-512x160.png)

Declarative, composable HTTP route definitions for PHP 8.4+, built on [Slim](https://www.slimframework.com/).

[![Latest Version](https://img.shields.io/packagist/v/oihana/php-routes.svg?style=flat-square)](https://packagist.org/packages/oihana/php-routes)  
[![Total Downloads](https://img.shields.io/packagist/dt/oihana/php-routes.svg?style=flat-square)](https://packagist.org/packages/oihana/php-routes)  
[![License](https://img.shields.io/packagist/l/oihana/php-routes.svg?style=flat-square)](LICENSE)

## 📚 Documentation

User guides (FR + EN), with narrative explanations and examples:

| 🇬🇧 **[English documentation](wiki/en/README.md)**                            | 🇫🇷 **[Documentation française](wiki/fr/README.md)**                          |
|-------------------------------------------------------------------------------|-------------------------------------------------------------------------------|
| Getting started, routes, HTTP method routes, document & i18n routes, helpers, testing. | Démarrage, routes, routes par méthode HTTP, routes document & i18n, helpers, tests. |

Auto-generated API reference (phpDocumentor):  
👉 https://bcommebois.github.io/oihana-php-routes

## 🧠 What is it?

`oihana/php-routes` provides a small, declarative layer for defining HTTP routes
on top of Slim: a composable `Route` base, one class per HTTP verb
(`GetRoute`, `PostRoute`, …), higher-level `DocumentRoute` / `I18nRoute`, and
helpers to register them on a Slim app from a PSR-11 container. Each route maps
a URL pattern to a controller method.

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

For full details (route classes, flags, helpers), see the table of contents in
the [documentation](wiki/en/README.md).

## 🚀 Features

- 🧭 A composable `Route` base and per-verb route classes — `GetRoute`, `PostRoute`, `PutRoute`, `PatchRoute`, `DeleteRoute`, `DeleteAllRoute`, `OptionsRoute`, `ListRoute`.
- 📄 Higher-level routes — `DocumentRoute` and `I18nRoute` for documents and localized paths.
- 🚩 Declarative route options via the `RouteFlag` bit flags.
- 🧩 Route registration helpers — `HttpMethodRoutesTrait`, `HasRouteTrait`, plus `withPlaceholder()` and `responsePassthrough()`.
- 🔗 Built on Slim and wired through a PSR-11 container.
- 🧪 Full unit-test coverage ensuring reliability and maintainability.

💡 Designed to be composable, testable, and compatible with any PHP 8.4+ project.

## 📦 Installation

> **Requires [PHP 8.4+](https://php.net/releases/)**

Install via [Composer](https://getcomposer.org):
```bash
composer require oihana/php-routes
```

> `oihana/php-routes` depends on [`oihana/php-controllers`](https://github.com/BcommeBois/oihana-php-controllers),
> which requires the **`ext-imagick`** extension — make sure it is installed.

## ✅ Tests & coverage

Run the full unit-test suite (PHPUnit, strict mode):
```bash
composer test
```

Run a single test case:
```bash
./vendor/bin/phpunit --filter GetRouteTest
```

Measure coverage (requires Xdebug or PCOV):
```bash
composer coverage        # text + Clover + HTML under build/coverage/
composer coverage:md     # readable Markdown summary (build/coverage/COVERAGE.md)
```

The suite runs in **strict mode** and targets **100% line coverage**.

## 🧾 License

This project is licensed under the [Mozilla Public License 2.0 (MPL-2.0)](https://www.mozilla.org/en-US/MPL/2.0/).

## 👤 About the author

* Author : Marc ALCARAZ (aka eKameleon)
* Mail : marc@ooop.fr
* Website : http://www.ooop.fr

## 🛠️ Generate the Documentation

We use [phpDocumentor](https://phpdoc.org/) to generate the documentation into the ./docs folder.

### Usage
Run the command : 
```bash
composer doc
```

## 🔗 Related packages

- [oihana/php-controllers](https://github.com/BcommeBois/oihana-php-controllers) – the HTTP controllers the routes dispatch to (provides `AppTrait`).
- [oihana/php-core](https://github.com/BcommeBois/oihana-php-core) – core helpers and utilities used by this library.
- [oihana/php-enums](https://github.com/BcommeBois/oihana-php-enums) – strongly-typed constant enumerations (HTTP methods, etc.).
