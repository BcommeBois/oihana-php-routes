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

/**
 * Provides the per-verb route factory methods shared by composite routes.
 *
 * Building on {@see HasRouteTrait}, this trait exposes one helper per HTTP verb
 * (`get()`, `post()`, `put()`, `patch()`, `delete()`, `list()`, `count()`,
 * `options()`) that appends the matching {@see oihana\routes\http\HttpMethodRoute}
 * subclass to a routes accumulator, but only when the corresponding
 * {@see oihana\routes\enums\RouteFlag} is enabled. Default controller method
 * names can be overridden globally through {@see HttpMethodRoutesTrait::initializeMethods()}
 * or per call.
 *
 * @package oihana\routes\traits
 * @author  Marc Alcaraz (ekameleon)
 * @since   1.0.0
 */
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
     * @param array $init Initialization or override parameters, keyed by HTTP verb.
     * @return static Returns the current instance for method chaining.
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
     *
     * Only appended when the COUNT flag is enabled; the `/count` suffix is added
     * automatically to the given route.
     *
     * @param array       $routes Reference to the routes accumulator to append to.
     * @param string      $route  The base route path.
     * @param string|null $method The controller method to invoke (defaults to the `count` method).
     * @return void
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
     *
     * Only appended when the DELETE flag is enabled.
     *
     * @param array       $routes Reference to the routes accumulator to append to.
     * @param string      $route  The route path to register.
     * @param string|null $method The controller method to invoke (defaults to the configured delete method).
     * @return void
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
     *
     * Only appended when the GET flag is enabled.
     *
     * @param array       $routes Reference to the routes accumulator to append to.
     * @param string      $route  The route path to register.
     * @param string|null $method The controller method to invoke (defaults to the configured get method).
     * @return void
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
     *
     * Only appended when the LIST flag is enabled.
     *
     * @param array       $routes Reference to the routes accumulator to append to.
     * @param string      $route  The route path to register.
     * @param string|null $method The controller method to invoke (defaults to the `list` method).
     * @return void
     */
    public function list( array &$routes , string $route , ?string $method = HttpMethod::list ) :void
    {
        if( $this->hasList() )
        {
            $this->method( GetRoute::class , $routes , $route , $method ?? $this->list ) ;
        }
    }

    /**
     * Instantiates a route of the given class and appends it to the accumulator.
     *
     * @protected
     * @param string  $clazz  The fully qualified route class to instantiate (must extend {@see Route}).
     * @param array   $routes Reference to the routes accumulator to append to.
     * @param string  $route  The route path to register.
     * @param ?string $method The controller method to invoke for this route, if any.
     * @return void
     *
     * @throws InvalidArgumentException If the given class is not a subclass of {@see Route}.
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
     * The route is only appended when `$flag` is `true`, which lets callers
     * conditionally expose the OPTIONS verb depending on the other enabled routes.
     *
     * @param array  $routes Reference to the routes accumulator to append to.
     * @param string $route  The route path to register.
     * @param bool   $flag   Whether the OPTIONS route should actually be created (default `true`).
     * @return void
     *
     * @throws DependencyException If a dependency cannot be resolved by the container.
     * @throws NotFoundException If the requested entry is not found in the container.
     * @throws ContainerExceptionInterface If the container encounters an error while retrieving an entry.
     * @throws NotFoundExceptionInterface If no entry was found in the container for the given identifier.
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
     *
     * Only appended when the PATCH flag is enabled.
     *
     * @param array       $routes Reference to the routes accumulator to append to.
     * @param string      $route  The route path to register.
     * @param string|null $method The controller method to invoke (defaults to the configured patch method).
     * @return void
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
     *
     * Only appended when the POST flag is enabled.
     *
     * @param array       $routes Reference to the routes accumulator to append to.
     * @param string      $route  The route path to register.
     * @param string|null $method The controller method to invoke (defaults to the configured post method).
     * @return void
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
     *
     * Only appended when the PUT flag is enabled.
     *
     * @param array       $routes Reference to the routes accumulator to append to.
     * @param string      $route  The route path to register.
     * @param string|null $method The controller method to invoke (defaults to the configured put method).
     * @return void
     */
    public function put( array &$routes , string $route , ?string $method = null ) :void
    {
        if( $this->hasPut() )
        {
            $this->method( PutRoute::class , $routes , $route , $method ?? $this->put ) ;
        }
    }
}