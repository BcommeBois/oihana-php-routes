# Routes document & i18n

![Langue](https://img.shields.io/badge/langue-Français-blue)

Les classes par verbe ([Routes par méthode HTTP](http-routes.md)) enregistrent **une** route chacune. La plupart des ressources ont pourtant besoin d'une *famille* d'endpoints partageant le même contrôleur et les mêmes conventions d'URL : lister, compter, créer, lire, mettre à jour et supprimer un document ; ou exposer une unique propriété localisée dans plusieurs langues.

`oihana/php-routes` fournit deux sous-classes de `Route` de plus haut niveau pour exactement ces cas :

- `DocumentRoute` — enregistre un jeu complet de routes CRUD pour une ressource document en un seul `__invoke()`.
- `I18nRoute` — étend `DocumentRoute` pour enregistrer des routes localisées (i18n) pour une propriété d'un document.

Toutes deux réutilisent les mêmes clés `init` que la classe de base [`Route`](routes.md) et que les classes par verbe : vous n'écrivez donc jamais de chaînes magiques. Les clés sont définies dans `oihana\routes\Route` (`Route::CONTROLLER_ID === 'controllerID'`, `Route::ROUTE === 'route'`, `Route::PROPERTY === 'property'`, …).

## DocumentRoute

`oihana\routes\DocumentRoute` étend `Route` et compose `HttpMethodRoutesTrait`. Là où un `GetRoute` ou un `PostRoute` enregistre un seul endpoint, un `DocumentRoute` enregistre toute la surface CRUD d'une ressource en un seul appel, en appliquant deux conventions de placeholder :

- **Endpoints de collection** sur la route nue — `LIST`, `COUNT`, `POST` et un `OPTIONS` associé sur `/route`.
- **Endpoints de document** sur la route suivie d'un placeholder d'identifiant — `GET`, `PATCH`, `PUT` et `DELETE` sur `/route/{id:[0-9]+}`, avec leur propre `OPTIONS`. Le placeholder d'identifiant provient de la clé `routePattern` (défaut `id:[0-9]+`) et est ajouté par le [helper](helpers.md) `withPlaceholder()`.

Lorsque `hasDeleteMultiple()` est activé, la route `DELETE` est enregistrée avec un segment d'identifiant **optionnel** — `/route[/{id:[0-9]+}]` — afin que le même endpoint puisse supprimer un ou plusieurs documents.

Chaque verbe n'est émis que si son flag est activé (voir [Flags](flags.md)) ; si aucun flag de méthode n'est actif, rien n'est enregistré. Si le `controllerID` n'est pas présent dans le conteneur DI, `__invoke()` journalise un avertissement et n'enregistre rien.

### Clés `init`

`DocumentRoute` lit les clés de base de `Route` ainsi que les clés de flag/méthode gérées par `HttpMethodRoutesTrait` :

| Clé | Constante | Rôle |
|---|---|---|
| `controllerID` | `Route::CONTROLLER_ID` | Identifiant de service du contrôleur résolu depuis le conteneur. |
| `route` | `Route::ROUTE` | Chemin de route de base, ex. `'users'` → `/users`. |
| `routePattern` | `Route::ROUTE_PLACEHOLDER` | Placeholder d'identifiant ajouté aux endpoints de document (défaut `id:[0-9]+`). |
| `flags` | `Route::FLAGS` | Bits `RouteFlag` sélectionnant les verbes activés. |
| `name` / `prefix` / `suffix` | `Route::NAME` / `Route::PREFIX` / `Route::SUFFIX` | Composition optionnelle du nom de route. |

Le constructeur appelle `initializeFlags()` puis `initializeMethods()` : vous pouvez donc passer `flags` comme valeur `RouteFlag` et, optionnellement, des surcharges de méthode de contrôleur par verbe via les clés `HttpMethod`.

### Exemple

Basé sur `DocumentRouteTest` : un unique contrôleur anonyme expose les méthodes CRUD, et `RouteFlag::DEFAULT` active les verbes standard.

```php
use DI\Container;
use Slim\App;
use Slim\Factory\AppFactory;

use oihana\routes\DocumentRoute;
use oihana\routes\Route;
use oihana\routes\enums\RouteFlag;

$container = new Container();
AppFactory::setContainer( $container );
$app = AppFactory::create();
$container->set( App::class , $app );

// Le contrôleur exposant une méthode par verbe.
$container->set( 'my.controller' , new class
{
    public function get()    : string { return 'get'    ; }
    public function list()   : string { return 'list'   ; }
    public function count()  : string { return 'count'  ; }
    public function post()   : string { return 'post'   ; }
    public function patch()  : string { return 'patch'  ; }
    public function put()    : string { return 'put'    ; }
    public function delete() : string { return 'delete' ; }
} );

$route = new DocumentRoute( $container ,
[
    Route::CONTROLLER_ID => 'my.controller' ,
    Route::ROUTE         => 'users' ,
    Route::FLAGS         => RouteFlag::DEFAULT ,
]);

$route(); // enregistre le jeu complet de routes CRUD sur l'application Slim

// /users            -> LIST / COUNT / POST / OPTIONS
// /users/{id:[0-9]+} -> GET / PATCH / PUT / DELETE / OPTIONS
```

Avec `RouteFlag::NONE`, aucun flag de verbe n'est activé et `__invoke()` n'enregistre rien.

## I18nRoute

`oihana\routes\I18nRoute` étend `DocumentRoute` et est conçue pour l'accès **localisé** à une unique propriété d'un document. Au lieu d'une surface CRUD complète, elle enregistre un jeu de routes ciblé sous un segment de propriété :

```
/route/{id:[0-9]+}/property
```

Le chemin est construit à partir de `getRoute()`, du placeholder d'identifiant (`routePattern`, défaut `id:[0-9]+`) appliqué par `withPlaceholder()`, et de la clé `property`. Sur ce chemin, elle enregistre :

- une route `OPTIONS` ;
- une route `GET` liée à la méthode de contrôleur nommée d'après la `property` ;
- une route `PATCH` liée à `patch` + ucfirst(property) (par exemple, propriété `title` → méthode de contrôleur `patchTitle`).

Cela permet à une couche de localisation de lire et de mettre à jour un champ (un `title`, `description`, … traduisible) par document, la langue elle-même étant typiquement résolue depuis la requête (en-têtes, query, ou un segment de chemin de votre configuration de routage). Comme pour `DocumentRoute`, un `controllerID` absent du conteneur fait journaliser un avertissement à `__invoke()`, qui n'enregistre alors rien.

### Clés `init`

| Clé | Constante | Rôle |
|---|---|---|
| `controllerID` | `Route::CONTROLLER_ID` | Identifiant de service du contrôleur résolu depuis le conteneur. |
| `route` | `Route::ROUTE` | Chemin de route de base, ex. `'articles'` → `/articles`. |
| `property` | `Route::PROPERTY` | Le segment de propriété localisée et la base du nom de méthode, ex. `'title'`. |
| `routePattern` | `Route::ROUTE_PLACEHOLDER` | Placeholder d'identifiant (défaut `id:[0-9]+`). |

### Exemple

Basé sur `I18nRouteTest` : le contrôleur expose `get()` et `patch()`, et la route cible la propriété `title`.

```php
use DI\Container;
use Slim\App;
use Slim\Factory\AppFactory;

use oihana\routes\I18nRoute;
use oihana\routes\Route;

$container = new Container();
AppFactory::setContainer( $container );
$app = AppFactory::create();
$container->set( App::class , $app );

$container->set( 'my.controller' , new class
{
    public function get()   : string { return 'get'   ; }
    public function patch() : string { return 'patch' ; }
} );

$route = new I18nRoute( $container ,
[
    Route::CONTROLLER_ID => 'my.controller' ,
    Route::ROUTE         => 'articles' ,
    Route::PROPERTY      => 'title' ,
]);

$route(); // enregistre les routes de propriété localisée sur l'application Slim

// /articles/{id:[0-9]+}/title -> OPTIONS / GET / PATCH
```

## Voir aussi

- [Routes](routes.md) — la base `Route`, son cycle de vie et les traits d'enregistrement.
- [Routes par méthode HTTP](http-routes.md) — les classes de route par verbe bâties sur `HttpMethodRoute`.
- [Helpers](helpers.md) — les fonctions libres autochargées, dont `withPlaceholder()`.
- [Index de la documentation](README.md) — retour à la table des matières.
