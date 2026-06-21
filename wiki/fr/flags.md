# Flags

`oihana\routes\enums\RouteFlag` est le catalogue des **constantes de drapeaux
binaires** (bit-flags) qui déterminent quelles routes HTTP un objet de route
active (GET, POST, DELETE, …). Ce n'est **pas** un `enum` PHP natif : comme les
autres énumérations *oihana*, c'est une simple classe qui compose
`oihana\reflect\traits\ConstantsTrait`, si bien que ses valeurs restent de
simples `int` que vous pouvez combiner et stocker partout, tout en demeurant
introspectables.

Chaque drapeau est un bit distinct, puissance de deux (`1 << 0`, `1 << 1`, …),
ce qui permet de loger plusieurs drapeaux dans un même entier appelé **masque
binaire** (bitmask). On les combine avec l'opérateur OU binaire `|`, et on teste
la présence d'un drapeau dans un masque avec `oihana\core\bits\hasFlag()` :

```php
use oihana\routes\enums\RouteFlag;
use function oihana\core\bits\hasFlag;

// Combine deux drapeaux dans un même masque :
$mask = RouteFlag::GET | RouteFlag::POST ;

// Teste le masque :
hasFlag( $mask , RouteFlag::GET );   // true
hasFlag( $mask , RouteFlag::PATCH ); // false
```

Parce qu'elle compose `ConstantsTrait`, la classe `RouteFlag` expose aussi une
petite API de réflexion (`RouteFlag::enums()`, `RouteFlag::getConstants()`, …),
ainsi que quelques utilitaires dédiés : `RouteFlag::describe()` (libellé
lisible), `RouteFlag::getFlags()` (décompose un masque en ses drapeaux
individuels), `RouteFlag::has()` (même test que `hasFlag()`),
`RouteFlag::isValid()` (rejette les bits inconnus) et
`RouteFlag::convertLegacyArray()` (convertit l'ancien tableau de booléens
`hasGet`/`hasPost`/… en masque).

## Constantes

### Drapeaux de route individuels

Chaque constante ci-dessous active une seule route. Ce sont les briques de base
que l'on combine avec `|`.

| Constante | Valeur | Signification |
|---|---|---|
| `RouteFlag::NONE` | `0` | Aucune route activée. |
| `RouteFlag::COUNT` | `1` (`1 << 0`) | Active la route COUNT. |
| `RouteFlag::DELETE` | `2` (`1 << 1`) | Active la route DELETE. |
| `RouteFlag::DELETE_MULTIPLE` | `4` (`1 << 2`) | Active le DELETE avec prise en charge de plusieurs identifiants. |
| `RouteFlag::GET` | `8` (`1 << 3`) | Active la route GET. |
| `RouteFlag::LIST` | `16` (`1 << 4`) | Active la route LIST. |
| `RouteFlag::PATCH` | `32` (`1 << 5`) | Active la route PATCH. |
| `RouteFlag::POST` | `64` (`1 << 6`) | Active la route POST. |
| `RouteFlag::PUT` | `128` (`1 << 7`) | Active la route PUT. |

### Préréglages composites

Ces constantes sont des masques pré-combinés correspondant aux politiques les
plus courantes.

| Constante | Valeur | Signification |
|---|---|---|
| `RouteFlag::ALL` | `255` | Tous les drapeaux valides combinés (utilisé pour la validation par `isValid()`). |
| `RouteFlag::DEFAULT` | `255` | Routes par défaut : toutes les opérations CRUD activées (`COUNT \| DELETE \| DELETE_MULTIPLE \| GET \| LIST \| PATCH \| POST \| PUT`). |
| `RouteFlag::READ_ONLY` | `25` | Routes en lecture seule : `GET \| LIST \| COUNT`. |
| `RouteFlag::WRITE` | `230` | Routes en écriture : `POST \| PUT \| PATCH \| DELETE \| DELETE_MULTIPLE`. |
| `RouteFlag::CRUD` | `218` | CRUD de base sans le comptage : `GET \| LIST \| POST \| PUT \| DELETE`. |

### Constantes de réflexion / héritage (legacy)

`RouteFlag` déclare également quelques constantes `string` et `array` utilisées
en interne.

| Constante | Valeur | Signification |
|---|---|---|
| `RouteFlag::DEFAULT_FLAG` | `'defaultFlag'` | Clé du tableau legacy contrôlant l'état par défaut lors de la conversion via `convertLegacyArray()`. |
| `RouteFlag::HAS_COUNT` | `'hasCount'` | Clé booléenne legacy associée à `COUNT`. |
| `RouteFlag::HAS_DELETE` | `'hasDelete'` | Clé booléenne legacy associée à `DELETE`. |
| `RouteFlag::HAS_DELETE_MULTIPLE` | `'hasDeleteMultiple'` | Clé booléenne legacy associée à `DELETE_MULTIPLE`. |
| `RouteFlag::HAS_GET` | `'hasGet'` | Clé booléenne legacy associée à `GET`. |
| `RouteFlag::HAS_LIST` | `'hasList'` | Clé booléenne legacy associée à `LIST`. |
| `RouteFlag::HAS_PATCH` | `'hasPatch'` | Clé booléenne legacy associée à `PATCH`. |
| `RouteFlag::HAS_POST` | `'hasPost'` | Clé booléenne legacy associée à `POST`. |
| `RouteFlag::HAS_PUT` | `'hasPut'` | Clé booléenne legacy associée à `PUT`. |
| `RouteFlag::FLAGS` | `[…]` | La liste ordonnée des valeurs de drapeaux individuels (utilisée par `getFlags()`). |
| `RouteFlag::FLAGS_NAME` | `[…]` | Table valeur de drapeau → nom (utilisée par `describe()`). |

## Exemple

Combinaison de drapeaux avec `|`, puis lecture par une route via
`oihana\routes\traits\HasRouteTrait` (le trait qu'utilise chaque route HTTP
pour exposer `hasGet()`, `hasPost()`, … à partir de son masque `flags`) :

```php
<?php

require 'vendor/autoload.php';

use oihana\routes\enums\RouteFlag;
use oihana\routes\traits\HasRouteTrait;

use function oihana\core\bits\hasFlag;

// Un petit objet qui consomme les drapeaux via le trait partagé.
class FakeRoute
{
    use HasRouteTrait ;
}

$route = new FakeRoute() ;

// Compose un masque en lecture seule, plus la création de nouveaux documents :
$route->initializeFlags( RouteFlag::READ_ONLY | RouteFlag::POST ) ;

// En interne, le trait lit le masque avec hasFlag() :
var_dump( $route->hasGet() );    // bool(true)   — GET fait partie de READ_ONLY
var_dump( $route->hasList() );   // bool(true)   — LIST fait partie de READ_ONLY
var_dump( $route->hasPost() );   // bool(true)   — ajouté explicitement
var_dump( $route->hasDelete() ); // bool(false)  — jamais activé

// Vous pouvez tester le masque brut vous-même, comme le fait le trait :
var_dump( hasFlag( $route->flags , RouteFlag::COUNT ) ); // bool(true)

// Libellé lisible de tout ce qui est activé :
echo $route->describeFlags() , PHP_EOL ; // COUNT, GET, LIST, POST

// Active/désactive des drapeaux à l'exécution :
$route->enableFlags( RouteFlag::DELETE ) ;  // active DELETE
$route->disableFlags( RouteFlag::COUNT ) ;  // désactive COUNT
echo RouteFlag::describe( $route->flags ) , PHP_EOL ; // DELETE, GET, LIST, POST
```

Lorsqu'une route HTTP est enregistrée, ce sont les mêmes prédicats
`hasGet()`/`hasPost()`/… qui décident quels points d'entrée sont effectivement
câblés sur l'application Slim — voir [Routes par méthode HTTP](http-routes.md).

## Voir aussi

- [Routes](routes.md) — la base `Route`, son cycle de vie et les traits d'enregistrement.
- [Routes par méthode HTTP](http-routes.md) — les classes de route par verbe qui lisent ces drapeaux.
- [Index de la documentation](README.md) — retour à la table des matières.
