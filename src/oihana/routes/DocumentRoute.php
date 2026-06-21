<?php

namespace oihana\routes;

use DI\Container;
use DI\DependencyException;
use DI\NotFoundException;

use oihana\routes\traits\HttpMethodRoutesTrait;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;
use function oihana\routes\helpers\withPlaceholder;

/**
 * Registers a full set of CRUD routes for a single document resource.
 *
 * A `DocumentRoute` expands a base route into the conventional REST endpoints
 * for a resource, driven by the enabled {@see oihana\routes\enums\RouteFlag} bitmask:
 *
 * - Collection URL (`/route`): `LIST`, `COUNT`, `OPTIONS` and `POST`.
 * - Item URL (`/route/{id:[0-9]+}`): `OPTIONS`, `DELETE`, `GET`, `PATCH` and `PUT`.
 *
 * When `DELETE_MULTIPLE` is enabled, the identifier placeholder of the delete
 * route is made optional (`/route[/{id:[0-9]+}]`). Each generated route is only
 * registered when its associated flag is set and when the configured controller
 * is available in the DI container.
 *
 * @package oihana\routes
 * @author  Marc Alcaraz (ekameleon)
 * @since   1.0.0
 */
class DocumentRoute extends Route
{
    /**
     * Creates a new DocumentRoute instance.
     *
     * @param Container $container The DI container reference.
     * @param array     $init      Optional route initialization array (see {@see Route::__construct()}),
     *                             also accepting `flags` and per-verb method overrides.
     *
     * @throws DependencyException If a dependency cannot be resolved by the container.
     * @throws NotFoundException If the requested entry is not found in the container.
     * @throws ContainerExceptionInterface If the container encounters an error while retrieving an entry.
     * @throws NotFoundExceptionInterface If no entry was found in the container for the given identifier.
     */
    public function __construct( Container $container , array $init = [] )
    {
        parent::__construct( $container , $init ) ;
        $this->initializeFlags   ( $init )
             ->initializeMethods ( $init ) ;
    }

    use HttpMethodRoutesTrait ;

    /**
     * Registers every enabled CRUD route for this document resource.
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
        if ( $this->container->has( $this->controllerID ) )
        {
            $routes = [] ;

            $route = $this->getRoute() ;

            // COUNT/LIST/POST : /route
            $this->list    ( $routes , $route ) ;
            $this->count   ( $routes , $route ) ;
            $this->options ( $routes , $route , $this->hasPost() ) ;
            $this->post    ( $routes , $route ) ;

            // DELETE/GET/PATCH/PUT : /route/{id:[0-9]+}
            $docRoute = withPlaceholder( $route , $this->routePlaceholder ) ;

            // DELETE (`hasDeleteMultiple` === true ) -> /route[/{id:[0-9]+}]
            $deleteRoute = withPlaceholder( $route , $this->routePlaceholder , $this->hasDeleteMultiple() ) ;

            $this->options ( $routes , $docRoute , $this->hasGet() || $this->hasDelete() || $this->hasPatch() || $this->hasPut() ) ;
            $this->delete  ( $routes , $deleteRoute ) ;
            $this->get     ( $routes , $docRoute    ) ;
            $this->patch   ( $routes , $docRoute    ) ;
            $this->put     ( $routes , $docRoute    ) ;

            if( count( $routes ) > 0 )
            {
                $this->execute( $routes ) ;
            }
        }
        else
        {
            $this->logger->warning( $this . ' invoke failed, the controller \'' . $this->controllerID . '\' is not registered in the DI container.' );
        }
    }
}