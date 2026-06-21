<?php

namespace oihana\routes;

use DI\DependencyException;
use DI\NotFoundException;

use oihana\enums\Char;
use oihana\enums\http\HttpMethod;

use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;

use function oihana\routes\helpers\withPlaceholder;

/**
 * Registers the routes used to read and update a localized (i18n) property of a document.
 *
 * Unlike {@see DocumentRoute}, which exposes the whole resource, an `I18nRoute`
 * targets a single translatable property nested under an item identifier. It
 * builds the pattern `/route/{id}/property` and registers the `OPTIONS`, `GET`
 * and `PATCH` verbs for it, the `GET`/`PATCH` controller methods being derived
 * from the configured property name.
 *
 * @package oihana\routes
 * @author  Marc Alcaraz (ekameleon)
 * @since   1.0.0
 */
class I18nRoute extends DocumentRoute
{
    /**
     * Registers the OPTIONS, GET and PATCH routes for the localized property.
     *
     * If the configured controller is not registered in the DI container, a
     * warning is logged and no route is created.
     *
     * @return void
     *
     * @throws DependencyException If a dependency cannot be resolved by the container.
     * @throws NotFoundException If the requested entry is not found in the container.
     * @throws ContainerExceptionInterface If the container encounters an error while retrieving an entry.
     * @throws NotFoundExceptionInterface If no entry was found in the container for the given identifier.
     */
    public function __invoke(): void
    {
        if ( !$this->container->has( $this->controllerID ) )
        {
            $this->logger->warning( $this . ' invoke failed, the controller \'' . $this->controllerID . '\' is not registered in the DI container.');
            return ;
        }

        $routes = [] ;

        // route/{id}/property
        $route = withPlaceholder( $this->getRoute() , $this->routePlaceholder ) . Char::SLASH . $this->property ;

        $this->options ( $routes , $route ) ;
        $this->get     ( $routes , $route , $this->property ) ;
        $this->patch   ( $routes , $route , HttpMethod::patch . ucfirst( $this->property ) ) ;

        if( count( $routes ) > 0 )
        {
            $this->execute( $routes );
        }
    }

}