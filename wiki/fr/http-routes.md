# Routes par méthode HTTP

![Langue](https://img.shields.io/badge/langue-Français-blue)

`HttpMethodRoute` est la classe de base des routes qui associent un unique verbe HTTP à une **méthode de contrôleur**.

Chaque sous-classe enregistre exactement un verbe HTTP sur l'application Slim (`GetRoute` → `GET`, `PostRoute` → `POST`, …) et, par convention, appelle une méthode de même nom sur le contrôleur résolu depuis le conteneur PSR-11. Cette convention est portée par la constante `INTERNAL_METHOD` : une `GetRoute` appelle `$controller->get()`, une `DeleteRoute` appelle `$controller->delete()`, et ainsi de suite — sauf si vous la surchargez via la clé d'initialisation `method`.

## `HttpMethodRoute`

`HttpMethodRoute` étend [`Route`](routes.md) et ajoute une unique propriété `string $method` ainsi que la mécanique d'enregistrement. Elle est **abstraite** : ce sont les sous-classes qui fournissent l'implémentation de `registerRoute()` propre à chaque verbe.

### La convention `INTERNAL_METHOD`

Chaque sous-classe redéfinit la constante :

```php
public const string INTERNAL_METHOD = HttpMethod::get ;
```

À la construction, `initializeMethod()` résout le nom de méthode effectif :

```php
$this->method = $init[ static::METHOD ] ?? static::INTERNAL_METHOD ;
```

La méthode du contrôleur vaut donc `INTERNAL_METHOD` par défaut, mais la clé d'initialisation `method` (la constante `Route::METHOD`, de valeur `'method'`) prévaut toujours lorsqu'elle est présente.

### La méthode patron `registerRoute()`

`__invoke()` est le point d'entrée. Il valide le câblage, puis délègue l'enregistrement effectif à la méthode patron abstraite `registerRoute()` :

1. il vérifie que `controllerID` est enregistré dans le conteneur — sinon, il journalise un avertissement et retourne sans rien enregistrer ;
2. il résout le contrôleur via `$this->container->get( $this->controllerID )` ;
3. il vérifie que le contrôleur expose bien la méthode résolue `$this->method` (via `method_exists`) — sinon il journalise un avertissement et retourne ;
4. enfin, il appelle `registerRoute( [ $controller , $this->method ] )` avec le gestionnaire sous la forme d'un callable `[$controller, 'method']`.

Chaque sous-classe implémente `registerRoute()` en appelant la méthode Slim correspondant au verbe et en nommant la route. Par exemple, `GetRoute` :

```php
protected function registerRoute( callable $handler ):void
{
    $this->app->get( $this->getRoute() , $handler )->setName( $this->getName() ) ;
}
```

Le motif de la route provient de `getRoute()` et son nom de `getName()`, tous deux hérités de [`Route`](routes.md).

### Exemple exécutable

```php
use DI\Container;
use Slim\Factory\AppFactory;
use Slim\App;

use oihana\routes\http\GetRoute;

$container = new Container();
AppFactory::setContainer( $container );
$app = AppFactory::create();
$container->set( App::class , $app );

// Un contrôleur exposant une méthode 'get'.
$controller = new class
{
    public function get(): string { return 'get-called'; }
};
$container->set( 'my.controller' , $controller );

// Enregistre : GET /api/test  ->  $controller->get()
$route = new GetRoute( $container , [
    'controllerID' => 'my.controller',
    'route'        => 'api/test',
] );

$route(); // résout le contrôleur et enregistre la route sur l'application Slim
```

## Classes par verbe

Toutes ces classes appartiennent à l'espace de noms `oihana\routes\http`.

| Classe           | Verbe HTTP | Méthode de contrôleur par défaut (`INTERNAL_METHOD`) |
| ---------------- | ---------- | ---------------------------------------------------- |
| `GetRoute`       | `GET`      | `get`                                                |
| `PostRoute`      | `POST`     | `post`                                               |
| `PutRoute`       | `PUT`      | `put`                                                |
| `PatchRoute`     | `PATCH`    | `patch`                                              |
| `DeleteRoute`    | `DELETE`   | `delete`                                             |
| `DeleteAllRoute` | `DELETE`   | `deleteAll`                                          |
| `OptionsRoute`   | `OPTIONS`  | — (voir ci-dessous)                                  |
| `ListRoute`      | `GET`      | `list`                                               |

Remarques :

- `DeleteAllRoute` étend `DeleteRoute` et ne surcharge que `INTERNAL_METHOD` vers `deleteAll`. Elle enregistre toujours un verbe `DELETE` — typiquement sur l'URL d'une collection (par exemple `DELETE /users` pour supprimer toutes les ressources, par opposition à `DELETE /users/{id}` pour une seule).
- `ListRoute` étend `GetRoute` et ne surcharge que `INTERNAL_METHOD` vers `list`. Elle enregistre toujours un verbe `GET`.
- `OptionsRoute` est particulière : elle étend directement [`Route`](routes.md) plutôt que `HttpMethodRoute`. Elle n'appelle **pas** de méthode de contrôleur ; elle enregistre à la place une route Slim `options()` à l'aide du helper `responsePassthrough`, qui retourne la réponse telle quelle (généralement modifiée par un middleware CORS pour les requêtes preflight).

## Surcharge de la méthode

La convention par défaut peut être surchargée route par route grâce à la clé d'initialisation `method`. Le gestionnaire est toujours résolu depuis le même `controllerID`, mais il cible la méthode que vous nommez :

```php
use oihana\routes\http\GetRoute;

// GET /foo  ->  $controller->fetch()   (au lieu du ->get() par défaut)
$route = new GetRoute( $container , [
    'controllerID' => 'my.controller',
    'route'        => 'foo',
    'method'       => 'fetch',
] );

$route();
```

Cela fonctionne pour toutes les sous-classes de `HttpMethodRoute`. Par exemple, une `DeleteAllRoute` avec `'method' => 'truncate'` enregistre un verbe `DELETE` qui appelle `$controller->truncate()`.

## Voir aussi

- [Routes](routes.md) — la classe de base `Route`, son cycle de vie et les traits d'enregistrement.
- [Routes document & i18n](document-i18n-routes.md) — `DocumentRoute` et `I18nRoute`.
- Retour à l'[index de la documentation](README.md).
