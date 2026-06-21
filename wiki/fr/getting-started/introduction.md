# Introduction

`oihana/php-routes` rassemble les briques de routage HTTP qui vivaient auparavant dans `oihana/php-system`, extraites dans un paquet dédié afin qu'un projet puisse dépendre de la couche de routage avec une surface de dépendances claire et déclarée.

Elle s'appuie sur [Slim](https://www.slimframework.com/) : au lieu d'enregistrer les routes avec des closures en ligne, vous les déclarez comme de petits objets `Route` composables qui associent un motif d'URL à une méthode de contrôleur et s'enregistrent eux-mêmes sur l'application Slim.

## Ce qu'elle fournit

| Composant | Type | Rôle |
|---|---|---|
| `Route` | classe | La route de base composable, reliée à un conteneur PSR-11. |
| `http\HttpMethodRoute` | classe | Base des classes par verbe (méthode template `registerRoute()`). |
| `http\GetRoute` / `PostRoute` / `PutRoute` / `PatchRoute` / `DeleteRoute` / `DeleteAllRoute` / `OptionsRoute` / `ListRoute` | classes | Une route par verbe HTTP. |
| `DocumentRoute` | classe | Une route de plus haut niveau pour les endpoints document. |
| `I18nRoute` | classe | Une variante de route pour les chemins localisés (i18n). |
| `traits\HasRouteTrait` / `HttpMethodRoutesTrait` | traits | Helpers d'enregistrement des routes. |
| `enums\RouteFlag` | classe | Options de route déclaratives sous forme de bit flags. |
| `helpers\responsePassthrough` / `withPlaceholder` | fonctions libres | Petits helpers de routage (autochargés). |

## La philosophie *oihana*

- **PHP 8.4+ uniquement** — constantes typées, aucun palliatif legacy.
- **Pas de *magic strings*** — les options de route sont des constantes typées (`RouteFlag`, méthodes HTTP) ; le projet n'utilise jamais d'enum natif PHP.
- **Composable** — chaque route est un petit objet ; verbes et conventions se composent librement.
- **Testée** — 100 % de couverture de lignes, mode strict PHPUnit (voir [Tests & couverture](../testing.md)).

## Étapes suivantes

- [Installation](installation.md)
- [Dépendances](dependencies.md)
- [Routes](../routes.md)
