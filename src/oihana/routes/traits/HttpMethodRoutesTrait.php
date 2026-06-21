<?php

namespace oihana\routes\traits;

use DI\DependencyException;
use DI\NotFoundException;

use InvalidArgumentException;

use oihana\enums\Char;
use oihana\enums\http\HttpMethod;
use oihana\routes\http\DeleteRoute;
use oihana\routes\http\GetRoute;
use oihana\routes\http\OptionsRoute;
use oihana\routes\http\PatchRoute;
use oihana\routes\http\PostRoute;
use oihana\routes\http\PutRoute;
use oihana\routes\Route;

use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;

use function oihana\core\arrays\clean;

trait HttpMethodRoutesTrait
{
    use HasRouteTrait ;

    public ?string $delete = null ;
    public ?string $get    = null ;
    public ?string $list   = null ;
    public ?string $patch  = null ;
    public ?string $post   = null ;
    public ?string $put    = null ;

    /**
     * Initializes or overrides HTTP method handlers based on the given init array.
     *
     * @param array $init Initialization or override parameters.
     * @return static
     */
    public function initializeMethods( array $init = [] ) :static
    {
        $this->delete = $init[ HttpMethod::delete ] ?? $this->delete ;
        $this->get    = $init[ HttpMethod::get    ] ?? $this->get    ;
        $this->list   = $init[ HttpMethod::list   ] ?? $this->list   ;
        $this->patch  = $init[ HttpMethod::patch  ] ?? $this->patch  ;
        $this->post   = $init[ HttpMethod::post   ] ?? $this->post   ;
        $this->put    = $init[ HttpMethod::put    ] ?? $this->put    ;
        return $this ;
    }

    /**
     * Generates a special GET count route reference.
     */
    public function count( array &$routes , string $route , ?string $method = HttpMethod::count ) :void
    {
        if( $this->hasCount() )
        {
            $this->method( GetRoute::class , $routes , $route . Char::SLASH . HttpMethod::count , $method ) ;
        }
    }

    /**
     * Generates a new DELETE route reference.
     */
    public function delete( array &$routes , string $route , ?string $method = null ) :void
    {
        if( $this->hasDelete() )
        {
            $this->method( DeleteRoute::class , $routes , $route , $method ?? $this->delete ) ;
        }
    }

    /**
     * Generates a new GET route reference.
     */
    public function get( array &$routes , string $route , ?string $method = null ) :void
    {
        if( $this->hasGet() )
        {
            $this->method( GetRoute::class , $routes , $route , $method ?? $this->get ) ;
        }
    }

    /**
     * Generates a new GET (LIST) route reference.
     */
    public function list( array &$routes , string $route , ?string $method = HttpMethod::list ) :void
    {
        if( $this->hasList() )
        {
            $this->method( GetRoute::class , $routes , $route , $method ?? $this->list ) ;
        }
    }

    /**
     * @protected
     * @param string $clazz
     * @param array $routes
     * @param string $route
     * @param ?string $method
     * @return void
     */
    public function method( string $clazz , array &$routes , string $route , ?string $method = null ) :void
    {
        if ( !is_subclass_of( $clazz , Route::class ) )
        {
            throw new InvalidArgumentException( "Invalid route class: $clazz" ) ;
        }

        $routes[] = new $clazz( $this->container , clean
        ([
            Route::CONTROLLER_ID => $this->controllerID ,
            Route::METHOD        => $method ,
            Route::ROUTE         => $route
        ]) ) ;
    }

    /**
     * Generates a new OPTIONS route reference.
     *
     * @param array $routes
     * @param string $route
     * @param bool $flag
     *
     * @throws DependencyException
     * @throws NotFoundException
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function options( array &$routes , string $route , bool $flag = true ) :void
    {
        if ( $flag )
        {
            $routes[] = new OptionsRoute
            (
                $this->container ,
                [
                    Route::CONTROLLER_ID => $this->controllerID ,
                    Route::ROUTE         => $route
                ]
            ) ;
        }
    }

    /**
     * Generates a new PATCH route reference.
     */
    public function patch( array &$routes , string $route , ?string $method = null ) :void
    {
        if( $this->hasPatch() )
        {
            $this->method( PatchRoute::class , $routes , $route , $method ?? $this->patch ) ;
        }
    }

    /**
     * Generates a new POST route reference.
     */
    public function post( array &$routes , string $route , ?string $method = null ) :void
    {
        if( $this->hasPost() )
        {
            $this->method( PostRoute::class , $routes , $route , $method ?? $this->post ) ;
        }
    }

    /**
     * Generates a new PUT route reference.
     */
    public function put( array &$routes , string $route , ?string $method = null ) :void
    {
        if( $this->hasPut() )
        {
            $this->method( PutRoute::class , $routes , $route , $method ?? $this->put ) ;
        }
    }
}