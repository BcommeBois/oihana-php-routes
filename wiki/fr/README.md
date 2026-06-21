# oihana/php-routes — routage HTTP déclaratif pour PHP

![Langue](https://img.shields.io/badge/langue-Français-blue)

`oihana/php-routes` est une bibliothèque PHP 8.4+ qui fournit une petite couche déclarative pour définir des routes HTTP au-dessus de [Slim](https://www.slimframework.com/) : une base `Route` composable, une classe par verbe HTTP, des routes document et i18n, et des helpers pour les enregistrer sur une application Slim depuis un conteneur PSR-11.

![Oihana PHP Routes](https://raw.githubusercontent.com/BcommeBois/oihana-php-routes/main/assets/images/oihana-php-routes-logo-inline-512x160.png)

## À qui s'adresse cette documentation

Aux développeurs PHP qui souhaitent :

- déclarer des routes HTTP comme de petits **objets composables** plutôt que des closures en ligne ;
- associer un motif d'URL à une **méthode de contrôleur** par verbe HTTP (`GetRoute`, `PostRoute`, …) ;
- construire des routes **document** et **localisées (i18n)** avec des conventions partagées ;
- enregistrer le tout sur une application Slim câblée via un **conteneur PSR-11**.

## Démarrage rapide

```php
use DI\Container;
use Slim\Factory\AppFactory;
use Slim\App;

use oihana\routes\http\GetRoute;

$container = new Container();
AppFactory::setContainer( $container );
$app = AppFactory::create();
$container->set( App::class , $app );

// Enregistre : GET /api/test  ->  $controller->get()
$route = new GetRoute( $container , [
    'controllerID' => 'my.controller',
    'route'        => 'api/test',
] );

$route(); // enregistre la route sur l'application Slim
```

Pour le détail complet, voir la table des matières ci-dessous.

## Table des matières

### Démarrage — [`getting-started/`](getting-started/)

- [Introduction](getting-started/introduction.md) — ce que fait la bibliothèque et la philosophie *oihana*.
- [Installation](getting-started/installation.md) — prérequis PHP 8.4+ et `composer require`.
- [Dépendances](getting-started/dependencies.md) — les paquets runtime et leur rôle.

### Utilisation

- [Routes](routes.md) — la base `Route`, son cycle de vie et les traits d'enregistrement.
- [Routes par méthode HTTP](http-routes.md) — les classes par verbe basées sur `HttpMethodRoute`.
- [Routes document & i18n](document-i18n-routes.md) — `DocumentRoute` et `I18nRoute`.
- [Helpers](helpers.md) — les fonctions libres autochargées.
- [Flags](flags.md) — les bit flags `RouteFlag`.

### Transverse

- [Tests & couverture](testing.md) — lancer la suite PHPUnit et mesurer la couverture.

## Code source

Le code de la bibliothèque se trouve sous [`src/oihana/routes/`](../../src/oihana/routes/) — namespace `oihana\routes`.

## Voir aussi

- [Packagist `oihana/php-routes`](https://packagist.org/packages/oihana/php-routes) — la page du paquet.
- [Référence API (phpDocumentor)](https://bcommebois.github.io/oihana-php-routes) — référence générée au niveau des classes.
