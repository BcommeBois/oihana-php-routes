# Dependencies

`oihana/php-routes` is a thin layer on top of the controller stack. Here is what
it requires and **why**.

## Oihana runtime dependencies

| Package | Role |
|---|---|
| [`oihana/php-controllers`](https://github.com/BcommeBois/oihana-php-controllers) | Provides `AppTrait` (the Slim `App` reference and URL building) that `Route` composes. Pulls the Slim/Twig controller stack transitively. |
| [`oihana/php-core`](https://github.com/BcommeBois/oihana-php-core) | Core helpers — `arrays\clean()`, `arrays\isAssociative()`, `bits\hasFlag()`, `strings\betweenBraces()` / `betweenBrackets()`. |
| [`oihana/php-enums`](https://github.com/BcommeBois/oihana-php-enums) | Typed constants — `Char`, `http\HttpMethod`. |
| [`oihana/php-logging`](https://github.com/BcommeBois/oihana-php-logging) | PSR-3 logging (`LoggerTrait`). |
| [`oihana/php-reflect`](https://github.com/BcommeBois/oihana-php-reflect) | `ConstantsTrait` for the typed-constant classes. |
| [`oihana/php-traits`](https://github.com/BcommeBois/oihana-php-traits) | Reusable object traits (`ContainerTrait`, `ToStringTrait`). |

## External runtime dependencies

| Package | Role |
|---|---|
| [`php-di/php-di`](https://packagist.org/packages/php-di/php-di) | PSR-11 DI container the routes are wired through. |
| [`psr/container`](https://packagist.org/packages/psr/container) | PSR-11 `ContainerInterface` contract. |
| [`psr/http-message`](https://packagist.org/packages/psr/http-message) | PSR-7 message interfaces. |

> Slim itself (`slim/slim`, `slim/psr7`, …) is pulled in transitively through
> `oihana/php-controllers`, together with its `ext-imagick` requirement.

## Development dependencies

| Package | Role |
|---|---|
| `phpunit/phpunit` | Test runner (strict mode). |
| `nunomaduro/collision` | Readable CLI error output. |
| `phpdocumentor/shim` | API documentation generation. |

## Next steps

- [Routes](../routes.md)
- [HTTP method routes](../http-routes.md)
