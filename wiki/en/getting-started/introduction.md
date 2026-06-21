# Introduction

`oihana/php-routes` gathers the HTTP routing building blocks that used to live inside `oihana/php-system`, extracted into a focused package so a project can depend on the routing layer with a clear, declared dependency surface.

It builds on [Slim](https://www.slimframework.com/): instead of registering routes with inline closures, you declare them as small, composable `Route` objects that map a URL pattern to a controller method and register themselves on the Slim app.

## What it provides

| Component | Type | Role |
|---|---|---|
| `Route` | class | The composable base route, wired to a PSR-11 container. |
| `http\HttpMethodRoute` | class | Base for the per-verb route classes (template method `registerRoute()`). |
| `http\GetRoute` / `PostRoute` / `PutRoute` / `PatchRoute` / `DeleteRoute` / `DeleteAllRoute` / `OptionsRoute` / `ListRoute` | classes | One route per HTTP verb. |
| `DocumentRoute` | class | A higher-level route for document endpoints. |
| `I18nRoute` | class | A route variant for localized (i18n) paths. |
| `traits\HasRouteTrait` / `HttpMethodRoutesTrait` | traits | Route registration helpers. |
| `enums\RouteFlag` | class | Declarative route options as bit flags. |
| `helpers\responsePassthrough` / `withPlaceholder` | free functions | Small routing helpers (autoloaded). |

## The *oihana* philosophy

- **PHP 8.4+ only** — typed constants, no legacy shims.
- **No *magic strings*** — route options are typed constants (`RouteFlag`, HTTP methods); the project never uses native PHP enums.
- **Composable** — each route is a small object; verbs and conventions compose freely.
- **Tested** — 100% line coverage, strict PHPUnit mode (see [Tests & coverage](../testing.md)).

## Next steps

- [Installation](installation.md)
- [Dependencies](dependencies.md)
- [Routes](../routes.md)
