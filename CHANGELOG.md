# Changelog

All notable changes to this project will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.0.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [1.0.0] - 2026-06-21

First release. The `oihana\routes` namespace is extracted from
`oihana/php-system` into its own focused HTTP-routing package for PHP 8.4+,
built on [Slim](https://www.slimframework.com/).

### Added
- Project scaffolding: `composer.json`, `phpunit.xml`, `phpdoc.xml`,
  CI and Docs GitHub workflows (with the `imagick`, `zip` and `fileinfo`
  extensions enabled for the transitive `oihana/php-controllers` dependency),
  coverage tooling, phpDocumentor template, README, CONTRIBUTING and license.
- Brand assets (logos) under `assets/images/`.
- The `oihana\routes` library, imported from `oihana/php-system`
  (identical FQNs):
  - `Route` — the composable base route wired to a PSR-11 container.
  - `http\HttpMethodRoute` and the per-verb route classes `http\GetRoute`,
    `http\PostRoute`, `http\PutRoute`, `http\PatchRoute`, `http\DeleteRoute`,
    `http\DeleteAllRoute`, `http\OptionsRoute`, `http\ListRoute`,
    `http\SearchRoute` (a `GET` route dispatching to the controller's
    `search()` method, like `ListRoute` for `list()`).
  - `DocumentRoute` and `I18nRoute` — higher-level document and localized routes.
  - `traits\HasRouteTrait` and `traits\HttpMethodRoutesTrait` — route
    registration helpers.
  - `enums\RouteFlag` — a `ConstantsTrait`-based bit-flag class (no native enum).
  - `helpers\responsePassthrough()` and `helpers\withPlaceholder()` free
    functions, wired via composer `autoload.files`.
- Unit-test suite imported from `oihana/php-system` (PHPUnit, strict mode).
  **100% line coverage** (194/194 lines, 46/46 methods, 13/13 classes), 85 tests.
- Bilingual user guide under `wiki/` (English + French): getting started
  (introduction, installation, dependencies), routes, HTTP method routes,
  document & i18n routes, helpers, flags and a testing guide.
