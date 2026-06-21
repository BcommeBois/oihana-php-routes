# Dépendances

`oihana/php-routes` est une fine couche au-dessus de la pile contrôleur. Voici ce
qu'elle requiert et **pourquoi**.

## Dépendances runtime oihana

| Paquet | Rôle |
|---|---|
| [`oihana/php-controllers`](https://github.com/BcommeBois/oihana-php-controllers) | Fournit `AppTrait` (la référence au `App` Slim et la construction d'URL) que `Route` compose. Tire la pile contrôleur Slim/Twig en transitif. |
| [`oihana/php-core`](https://github.com/BcommeBois/oihana-php-core) | Helpers de base — `arrays\clean()`, `arrays\isAssociative()`, `bits\hasFlag()`, `strings\betweenBraces()` / `betweenBrackets()`. |
| [`oihana/php-enums`](https://github.com/BcommeBois/oihana-php-enums) | Constantes typées — `Char`, `http\HttpMethod`. |
| [`oihana/php-logging`](https://github.com/BcommeBois/oihana-php-logging) | Journalisation PSR-3 (`LoggerTrait`). |
| [`oihana/php-reflect`](https://github.com/BcommeBois/oihana-php-reflect) | `ConstantsTrait` pour les classes à constantes typées. |
| [`oihana/php-traits`](https://github.com/BcommeBois/oihana-php-traits) | Traits d'objets réutilisables (`ContainerTrait`, `ToStringTrait`). |

## Dépendances runtime externes

| Paquet | Rôle |
|---|---|
| [`php-di/php-di`](https://packagist.org/packages/php-di/php-di) | Conteneur DI PSR-11 par lequel les routes sont câblées. |
| [`psr/container`](https://packagist.org/packages/psr/container) | Contrat PSR-11 `ContainerInterface`. |
| [`psr/http-message`](https://packagist.org/packages/psr/http-message) | Interfaces de messages PSR-7. |

> Slim lui-même (`slim/slim`, `slim/psr7`, …) est tiré en transitif via
> `oihana/php-controllers`, avec son prérequis `ext-imagick`.

## Dépendances de développement

| Paquet | Rôle |
|---|---|
| `phpunit/phpunit` | Lanceur de tests (mode strict). |
| `nunomaduro/collision` | Sortie d'erreurs CLI lisible. |
| `phpdocumentor/shim` | Génération de la documentation API. |

## Étapes suivantes

- [Routes](../routes.md)
- [Routes par méthode HTTP](../http-routes.md)
