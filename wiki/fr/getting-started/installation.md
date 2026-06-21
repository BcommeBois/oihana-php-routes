# Installation

## Prérequis

- **PHP 8.4 ou supérieur.**
- **[Composer](https://getcomposer.org/).**

`oihana/php-routes` dépend de [`oihana/php-controllers`](https://github.com/BcommeBois/oihana-php-controllers)
(elle compose son `AppTrait`), qui requiert à son tour l'extension
**`ext-imagick`** — celle-ci doit donc être disponible pour que `composer
install` se résolve et que la suite de tests s'exécute.

## Installation via Composer

```bash
composer require oihana/php-routes
```

## Autochargement

Les classes sont autochargées en PSR-4 sous le namespace `oihana\routes\`, et
les deux helpers de route via `autoload.files` de composer :

```json
{
    "autoload": {
        "psr-4": {
            "oihana\\routes\\": "src/oihana/routes"
        },
        "files": [
            "src/oihana/routes/helpers/responsePassthrough.php",
            "src/oihana/routes/helpers/withPlaceholder.php"
        ]
    }
}
```

Une fois installé, importez directement les classes de route :

```php
use oihana\routes\http\GetRoute;
use oihana\routes\http\PostRoute;
```

## Vérifier l'installation

```php
require 'vendor/autoload.php';

use DI\Container;
use Slim\Factory\AppFactory;
use Slim\App;
use oihana\routes\http\GetRoute;

$container = new Container();
AppFactory::setContainer( $container );
$container->set( App::class , AppFactory::create() );

$route = new GetRoute( $container , [ 'controllerID' => 'my.controller', 'route' => 'ping' ] );
```

## Étapes suivantes

- [Dépendances](dependencies.md)
- [Routes](../routes.md)
