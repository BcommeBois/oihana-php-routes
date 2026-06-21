<?php

namespace oihana\routes;

use DI\Container;
use DI\DependencyException;
use DI\NotFoundException;

use oihana\routes\traits\HttpMethodRoutesTrait;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;
use function oihana\routes\helpers\withPlaceholder;

class DocumentRoute extends Route
{
    public function __construct( Container $container , array $init = [] )
    {
        parent::__construct( $container , $init ) ;
        $this->initializeFlags   ( $init )
             ->initializeMethods ( $init ) ;
    }

    use HttpMethodRoutesTrait ;

    /**
     * Initialize the current route.
     * @throws DependencyException
     * @throws NotFoundException
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
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