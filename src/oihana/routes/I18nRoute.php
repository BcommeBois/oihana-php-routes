<?php

namespace oihana\routes;

use DI\DependencyException;
use DI\NotFoundException;

use oihana\enums\Char;
use oihana\enums\http\HttpMethod;

use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;

use function oihana\routes\helpers\withPlaceholder;

class I18nRoute extends DocumentRoute
{
    /**
     * @throws DependencyException
     * @throws NotFoundException
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
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