# Installation

## Requirements

- **PHP 8.4 or higher.**
- **[Composer](https://getcomposer.org/).**

`oihana/php-routes` depends on [`oihana/php-controllers`](https://github.com/BcommeBois/oihana-php-controllers)
(it composes its `AppTrait`), which in turn requires the **`ext-imagick`**
extension — so `ext-imagick` must be available for `composer install` to
resolve and for the test suite to run.

## Install via Composer

```bash
composer require oihana/php-routes
```

## Autoloading

Classes are autoloaded via PSR-4 under the `oihana\routes\` namespace, and the
two route helpers via composer `autoload.files`:

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

Once installed, import the route classes directly:

```php
use oihana\routes\http\GetRoute;
use oihana\routes\http\PostRoute;
```

## Verify the installation

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

## Next steps

- [Dependencies](dependencies.md)
- [Routes](../routes.md)
