# Helpers

Deux fonctions libres enregistrées via le mécanisme `autoload.files` de composer,
toutes deux dans l'espace de noms `oihana\routes\helpers`. Ce sont des fonctions
globales, et non des méthodes de classe : on importe chacune avec une instruction
`use function`, par exemple `use function oihana\routes\helpers\withPlaceholder;`.
La première produit un gestionnaire de requête PSR-15 « passthrough »
(`responsePassthrough()`), la seconde construit un motif de route compatible Slim
en ajoutant un segment de type placeholder (`withPlaceholder()`).

```php
use function oihana\routes\helpers\responsePassthrough;
use function oihana\routes\helpers\withPlaceholder;
```

Comme ce sont de simples fonctions, vous pouvez les appeler n'importe où — dans
une classe `Route`, un middleware ou un service autonome — sans étendre une
classe de base.

## responsePassthrough()

```php
function responsePassthrough(): callable
```

Retourne un gestionnaire de requête compatible PSR-15 qui renvoie simplement la
réponse inchangée. C'est utile pour les routes qui n'ont aucun traitement à
effectuer, comme les requêtes `OPTIONS` qui ne servent qu'à renvoyer des en-têtes
CORS, ou toute route dont la pile de middlewares prend déjà tout en charge.

Le callable retourné a la signature
`fn( ServerRequestInterface $request, ResponseInterface $response ): ResponseInterface`
et retourne toujours exactement la même instance `$response` que celle reçue, en
ignorant la `$request`.

```php
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

use function oihana\routes\helpers\responsePassthrough;

$handler = responsePassthrough();

// Le gestionnaire retourne l'objet réponse inchangé.
$result = $handler( $request, $response );
$result === $response; // true

// Utilisation typique sur une application Slim :
$app->options( '/api/users', responsePassthrough() );  // pré-vol CORS
$app->head( '/api/users', responsePassthrough() );     // miroir de GET
$app->get( '/health', responsePassthrough() );         // tout est géré par le middleware
```

## withPlaceholder()

```php
function withPlaceholder(
    string  $route,
    ?string $placeholder  = null,
    bool    $optional     = false,
    bool    $leadingSlash = true
): string
```

Construit une route compatible avec le framework Slim en ajoutant un segment de
type placeholder à un chemin de base. Elle gère automatiquement les slashs
finaux sur la route de base, les segments optionnels (crochets) et les
placeholders vides ou `null`.

- `$route` — le chemin de base de la route (par exemple `'/users'`).
- `$placeholder` — nom du placeholder, éventuellement avec une contrainte regex
  (par exemple `'id'`, `'id:[0-9]+'`, `'params:.*'`). Il peut déjà être entouré
  d'accolades (`'{id}'`) ; les accolades ne sont pas dupliquées.
- `$optional` — si `true`, le placeholder est entouré de crochets pour rendre le
  segment optionnel.
- `$leadingSlash` — si `true` (valeur par défaut), un slash initial est placé
  devant le placeholder.

```php
use function oihana\routes\helpers\withPlaceholder;

// Placeholder requis
withPlaceholder( '/users', 'id' );              // '/users/{id}'

// Placeholder requis avec une contrainte regex
withPlaceholder( '/users', 'id:[0-9]+' );       // '/users/{id:[0-9]+}'

// Placeholder optionnel (entouré de crochets)
withPlaceholder( '/users', 'id', true );        // '/users[/{id}]'
withPlaceholder( '/users', 'id:[0-9]+', true ); // '/users[/{id:[0-9]+}]'

// Placeholder « catch-all » multi-segments
withPlaceholder( '/news', 'params:.*' );        // '/news/{params:.*}'
withPlaceholder( '/news', 'params:.*', true );  // '/news[/{params:.*}]'

// Sans slash initial (usage avancé : collé directement à la base)
withPlaceholder( '/users', 'id', false, false ); // '/users{id}'
```

### Cas limites

```php
use function oihana\routes\helpers\withPlaceholder;

// Déjà entouré d'accolades — non dupliqué
withPlaceholder( '/users', '{id}' );               // '/users/{id}'
withPlaceholder( '/users', '{id:[0-9]+}', true );  // '/users[/{id:[0-9]+}]'

// Placeholder vide — la route de base est retournée inchangée
withPlaceholder( '/users', '' );                   // '/users'

// Placeholder null (ou omis) — la route de base est retournée inchangée
withPlaceholder( '/users' );                       // '/users'

// Le slash final de la route de base n'est pas dupliqué
withPlaceholder( '/path/', 'id' );                 // '/path/{id}'
```

## Voir aussi

- [Routes](routes.md) — la base `Route`, son cycle de vie et les traits d'enregistrement.
- [Routes par méthode HTTP](http-routes.md) — les classes de route par verbe construites sur `HttpMethodRoute`.
- [Index de la documentation](README.md) — retour à la table des matières.
