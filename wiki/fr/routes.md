# Routes

`oihana\routes\Route` est la classe de base composable sur laquelle repose toute
définition de route dans `oihana/php-routes`. Elle est reliée à un conteneur
[PSR-11](https://www.php-fig.org/psr/psr-11/) (le `DI\Container` de PHP-DI) : le
constructeur reçoit le conteneur et le stocke dans la propriété publique
`$container`, puis lit un tableau `$init` optionnel pour configurer la route.

Une `Route` associe un **motif d'URL** à une **méthode de contrôleur** et sait
**s'enregistrer elle-même sur l'application Slim** résolue depuis le conteneur.
Les classes concrètes par verbe (`GetRoute`, `PostRoute`, …) étendent `Route` via
la classe de base intermédiaire `HttpMethodRoute` et implémentent l'enregistrement
Slim proprement dit ; la classe de base `Route` fournit le cycle de vie commun
(nommage, normalisation du chemin, invocation des routes imbriquées) ainsi que les
briques sur lesquelles s'appuient les traits par verbe.

## Le cycle de vie d'une Route

### Constructeur et clés `init`

```php
public function __construct( DI\Container $container , array $init = [] )
```

Le constructeur appelle `initializeApp()` et `initializeLogger()` (qui résolvent
l'`App` Slim et un logger PSR-3 depuis le conteneur), puis lit les clés suivantes
dans `$init`. Chaque clé possède une constante `Route::` correspondante, ce qui
permet d'éviter les littéraux de chaînes en dur :

| Clé `init` (constante) | Chaîne | Propriété | Défaut |
| --- | --- | --- | --- |
| `Route::CONTROLLER_ID` | `'controllerID'` | `$controllerID` | `null` |
| `Route::NAME` | `'name'` | `$name` | `null` |
| `Route::OWNER_PLACEHOLDER` | `'ownerPlaceHolder'` | `$ownerPlaceholder` | `Route::DEFAULT_OWNER_PLACEHOLDER` (`'owner:[0-9]+'`) |
| `Route::PREFIX` | `'prefix'` | `$prefix` | `Route::DEFAULT_PREFIX` (`'api'`) |
| `Route::PROPERTY` | `'property'` | `$property` | `''` |
| `Route::ROUTE` | `'route'` | `$route` | `null` |
| `Route::ROUTE_PLACEHOLDER` | `'routePattern'` | `$routePlaceholder` | `Route::DEFAULT_ROUTE_PLACEHOLDER` (`'id:[0-9]+'`) |
| `Route::ROUTES` | `'routes'` | `$routes` | `null` |
| `Route::SUFFIX` | `'suffix'` | `$suffix` | `''` |

```php
use DI\Container;
use Slim\App;
use Slim\Factory\AppFactory;

use oihana\routes\Route;

$container = new Container() ;
AppFactory::setContainer( $container ) ;
$container->set( App::class , AppFactory::create() ) ;

$route = new Route( $container , [
    Route::CONTROLLER_ID => 'my.controller' ,
    Route::NAME          => 'foo' ,
    Route::PREFIX        => 'bar' ,
    Route::SUFFIX        => 'baz' ,
    Route::ROUTE         => '/api/test' ,
] ) ;

echo $route->name ;   // "foo"
echo $route->prefix ; // "bar"
echo $route->route ;  // "/api/test"
```

Lorsqu'aucun `init` n'est fourni, les valeurs par défaut s'appliquent :

```php
$route = new Route( $container ) ;

echo $route->prefix ;           // "api"
echo $route->ownerPlaceholder ; // "owner:[0-9]+"
echo $route->routePlaceholder ; // "id:[0-9]+"
```

### `getRoute()` — le chemin normalisé

```php
public function getRoute() : string
```

Retourne la route principale, toujours préfixée d'un unique `/` initial (tout
slash de tête de l'entrée est réduit). Lorsque `$route` vaut `null`, retourne `"/"`.

```php
$a = new Route( $container , [ Route::ROUTE => 'foo/bar' ] ) ;
echo $a->getRoute() ; // "/foo/bar"

$b = new Route( $container , [ Route::ROUTE => '/foo/bar' ] ) ;
echo $b->getRoute() ; // "/foo/bar"
```

### `dotify()` — des slashes aux points

```php
public function dotify( string $route ) : string
```

Convertit un chemin `'foo/bar'` en la forme `'foo.bar'` utilisée pour les noms de
route. Une chaîne sans slash est retournée inchangée.

```php
$route = new Route( $container ) ;
echo $route->dotify( 'foo/bar' ) ; // "foo.bar"
echo $route->dotify( 'foobar' ) ;  // "foobar"
```

### `getName()` — le nom de route qualifié

```php
public function getName() : string
```

Construit le nom de route qualifié et séparé par des points à partir de `$prefix`,
du `$name` explicite (ou de la route « dotifiée » lorsqu'aucun nom n'est fourni) et
de `$suffix`. Les segments vides sont supprimés.

```php
$named = new Route( $container , [
    Route::PREFIX => 'api' ,
    Route::NAME   => 'user.get' ,
    Route::SUFFIX => 'json' ,
] ) ;
echo $named->getName() ; // "api.user.get.json"

$fromRoute = new Route( $container , [ Route::ROUTE => 'foo/bar' ] ) ;
echo $fromRoute->getName() ; // "api.foo.bar"
```

### `create()` — construire une Route enfant depuis une définition

```php
public function create( array|Route|null $definition ) : ?Route
```

Normalise une définition de route en une instance de `Route` :

- un objet `Route` est retourné tel quel ;
- un tableau **associatif** est transformé en une nouvelle route de classe
  `$definition['clazz']` (par défaut `GetRoute::class`), héritant des
  `controllerID` et `route` du parent lorsque la définition les omet ;
- tout le reste (un tableau non associatif, `null`) retourne `null`.

```php
$parent = new Route( $container ) ;

$child = $parent->create( [
    Route::CLAZZ => Route::class ,
    Route::NAME  => 'foo' ,
    Route::ROUTE => '/test' ,
] ) ;

echo $child->name ;  // "foo"
echo $child->route ; // "/test"

$parent->create( null ) ;                   // null
$parent->create( [ 'not_associative' ] ) ;  // null
```

### `__invoke()` — enregistrer les routes imbriquées

```php
public function __invoke() : void
```

Lorsque `$routes` contient une ou plusieurs définitions imbriquées, chacune est
passée par `create()` puis invoquée, de sorte qu'appeler le parent enregistre tout
le sous-arbre sur l'application Slim. Sur la `Route` de base, c'est une opération
sans effet lorsque `$routes` est vide ; les sous-classes par verbe redéfinissent
`__invoke()` pour réaliser l'enregistrement Slim effectif.

```php
$child = new class( $container ) extends Route
{
    public bool $invoked = false ;
    public function __invoke() : void { $this->invoked = true ; }
} ;

$parent = new Route( $container , [ Route::ROUTES => [ $child ] ] ) ;
$parent() ; // exécute chaque route imbriquée

var_dump( $child->invoked ) ; // bool(true)
```

### `execute()` — exécuter un callable ou une liste de callables

```php
public function execute( mixed $routes ) : void
```

Un petit utilitaire qui invoque un callable unique, ou parcourt et invoque chaque
callable d'un tableau (les entrées non callables sont ignorées).

```php
$route  = new Route( $container ) ;
$called = 0 ;

$route->execute( fn() => $called++ ) ;                          // callable unique
$route->execute( [ fn() => $called++ , fn() => $called++ ] ) ;  // tableau

echo $called ; // 3
```

## Les traits d'enregistrement

Les classes de route par verbe ainsi que les routes document/i18n sont assemblées
à partir de deux traits que la `Route` de base n'utilise pas directement mais qui
opèrent sur ses propriétés (`$container`, `$controllerID`).

### `HasRouteTrait`

Porte le masque de bits des routes et les prédicats qui déterminent quelles
sous-routes sont activées. Il expose :

- `public int $flags` — le masque de bits des routes activées (défaut
  `RouteFlag::DEFAULT`).
- `initializeFlags( array|int $init = [] ) : static` — fixe `$flags` à partir d'un
  entier, de la clé `Route::FLAGS` d'un tableau, ou par conversion d'un tableau
  associatif hérité via `RouteFlag::convertLegacyArray()`.
- `hasCount()`, `hasDelete()`, `hasDeleteMultiple()`, `hasGet()`, `hasList()`,
  `hasPatch()`, `hasPost()`, `hasPut()` — des prédicats `bool` vérifiant un flag.
- `enableFlags( int $flags ) : static` / `disableFlags( int $flags ) : static` —
  active ou désactive des flags.
- `describeFlags() : string` — une description lisible des routes activées.

```php
use oihana\routes\enums\RouteFlag;
use oihana\routes\traits\HasRouteTrait;

$host = new class { use HasRouteTrait ; } ;

$host->initializeFlags( RouteFlag::GET | RouteFlag::LIST ) ;
var_dump( $host->hasGet() ) ;    // bool(true)
var_dump( $host->hasDelete() ) ; // bool(false)

$host->enableFlags( RouteFlag::DELETE ) ;
var_dump( $host->hasDelete() ) ; // bool(true)
```

### `HttpMethodRoutesTrait`

S'appuie sur `HasRouteTrait` et génère des instances de `Route` par verbe, en les
ajoutant à un tableau `&$routes` (uniquement lorsque le flag correspondant est
activé). Il détient les noms de méthodes de contrôleur surchargeables ainsi que
les générateurs :

- `public ?string $delete , $get , $list , $patch , $post , $put` — la méthode de
  contrôleur à appeler par verbe.
- `initializeMethods( array $init = [] ) : static` — remplit ces noms depuis les
  clés `HttpMethod::delete` / `get` / `list` / `patch` / `post` / `put`.
- `count()`, `delete()`, `get()`, `list()`, `patch()`, `post()`, `put()` — chacun
  ajoute la bonne classe de route (`GetRoute`, `DeleteRoute`, …) à `&$routes`
  lorsque son flag est positionné.
- `options( array &$routes , string $route , bool $flag = true ) : void` — ajoute
  une `OptionsRoute` (indépendamment des flags) sauf si `$flag` vaut `false`.
- `method( string $clazz , array &$routes , string $route , ?string $method = null ) : void` —
  le constructeur de bas niveau ; lève une `InvalidArgumentException` lorsque
  `$clazz` n'est pas une sous-classe de `Route`.

```php
use DI\Container;
use Slim\App;
use Slim\Factory\AppFactory;

use oihana\routes\enums\RouteFlag;
use oihana\routes\http\GetRoute;
use oihana\routes\traits\HttpMethodRoutesTrait;

$container = new Container() ;
AppFactory::setContainer( $container ) ;
$container->set( App::class , AppFactory::create() ) ;

$host = new class
{
    use HttpMethodRoutesTrait ;
    public Container $container ;
    public ?string $controllerID = 'my.controller' ;
} ;

$host->container = $container ;
$host->initializeFlags( RouteFlag::DEFAULT ) ;
$host->get = 'fetchAll' ;

$routes = [] ;
$host->get( $routes , '/users' ) ;

echo get_class( $routes[0] ) ; // "...\GetRoute"
echo $routes[0]->method ;      // "fetchAll"
```

## Voir aussi

- [Routes par méthode HTTP](http-routes.md) — les classes de route par verbe construites sur `HttpMethodRoute`.
- [Routes Document & i18n](document-i18n-routes.md) — `DocumentRoute` et `I18nRoute`.
- [Flags](flags.md) — les flags de bits `RouteFlag`.
- [Index de la documentation](README.md) — table des matières complète.
