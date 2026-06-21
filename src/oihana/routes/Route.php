<?php

namespace oihana\routes;

use DI\Container;
use DI\DependencyException;
use DI\NotFoundException;

use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;

use oihana\controllers\traits\AppTrait;
use oihana\logging\LoggerTrait;

use oihana\enums\Char;
use oihana\routes\http\GetRoute;
use oihana\traits\ContainerTrait;
use oihana\traits\ToStringTrait;

use function oihana\core\arrays\clean;
use function oihana\core\arrays\isAssociative;

/**
 * Represents a route definition and handles route creation and execution.
 */
class Route
{
    /**
     * Initializes a route instance with optional parameters.
     *
     * @param Container $container DI container
     * @param array $init Optional route initialization array:
     *  - 'controllerID': Optional controller identifier.
     *  - 'name': Optional route name (defaults to generated name).
     *  - 'ownerPattern': Optional owner route pattern (default '{owner:[0-9]+}').
     *  - 'prefix': Optional prefix for route name.
     *  - 'property': Optional property name in complex routes.
     *  - 'route': Optional main route pattern.
     *  - 'routePattern': Optional route regex pattern (default '{id:[0-9]+}').
     *  - 'routes': Optional nested route definitions to initialize.
     *  - 'suffix': Optional suffix for route name.
     *  - 'verbose': Optional verbose mode (default true).
     *
     * @throws DependencyException
     * @throws NotFoundException
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function __construct( Container $container , array $init = [] )
    {
        $this->container = $container ;

        $this->initializeApp    ( $init , $container )
             ->initializeLogger ( $init , $container ) ;

        $this->controllerID     = $init[ self::CONTROLLER_ID     ] ?? $this->controllerID ;
        $this->name             = $init[ self::NAME              ] ?? $this->name ;
        $this->ownerPlaceholder = $init[ self::OWNER_PLACEHOLDER ] ?? $this->ownerPlaceholder ;
        $this->prefix           = $init[ self::PREFIX            ] ?? $this->prefix ;
        $this->property         = $init[ self::PROPERTY          ] ?? $this->property ;
        $this->route            = $init[ self::ROUTE             ] ?? $this->route ;
        $this->routePlaceholder = $init[ self::ROUTE_PLACEHOLDER ] ?? $this->routePlaceholder ;
        $this->routes           = $init[ self::ROUTES            ] ?? $this->routes ;
        $this->suffix           = $init[ self::SUFFIX            ] ?? $this->suffix ;
    }

    use AppTrait ,
        ContainerTrait ,
        LoggerTrait ,
        ToStringTrait  ;

    /**
     * Default API prefix for route names
     */
    public const string DEFAULT_PREFIX = 'api' ;

    /**
     * Default route pattern for numeric IDs
     */
    public const string DEFAULT_ROUTE_PLACEHOLDER = 'id:[0-9]+' ;

    /**
     * Default owner pattern for numeric owner IDs
     */
    public const string DEFAULT_OWNER_PLACEHOLDER = 'owner:[0-9]+' ;

    /**
     * Array keys for route initialization
     */
    public const string APP                = 'app'              ;
    public const string CLAZZ              = 'clazz'            ;
    public const string CONTROLLER_ID      = 'controllerID'     ;
    public const string FLAGS              = 'flags'            ;
    public const string METHOD             = 'method'           ;
    public const string NAME               = 'name'             ;
    public const string OWNER_PATTERN      = 'ownerPattern'     ;
    public const string OWNER_PLACEHOLDER  = 'ownerPlaceHolder' ;
    public const string PATCH_PATTERN      = 'patchPattern'     ;
    public const string PATCH_PLACE_HOLDER = 'patchPlaceHolder' ;
    public const string PREFIX             = 'prefix'           ;
    public const string PROPERTY           = 'property'         ;
    public const string ROUTE              = 'route'            ;
    public const string ROUTE_PATTERN      = 'routePattern'     ;
    public const string ROUTE_PLACEHOLDER  = 'routePattern'     ;
    public const string ROUTES             = 'routes'           ;
    public const string SUFFIX             = 'suffix'           ;

    /**
     * @var string|null Controller ID registered in DI container
     */
    public ?string $controllerID = null ;

    /**
     * @var string|null Route name
     */
    public ?string $name = null ;

    /**
     * @var string|null The owner route placeholder.
     */
    public ?string $ownerPlaceholder = self::DEFAULT_OWNER_PLACEHOLDER ;

    /**
     * @var string Route name prefix
     */
    public string $prefix = self::DEFAULT_PREFIX ;

    /**
     * @var string Property name in complex routes
     */
    public string $property = Char::EMPTY ;

    /**
     * @var string|null The route expression.
     */
    public ?string $route = null ;

    /**
     * @var string The route placeholder.
     */
    public string $routePlaceholder = self::DEFAULT_ROUTE_PLACEHOLDER ;

    /**
     * @var array|null Nested route definitions.
     */
    public ?array $routes = null ;

    /**
     * @var string Route name suffix
     */
    public string $suffix = Char::EMPTY ;

    /**
     * Invokes all nested routes if defined.
     *
     * @throws DependencyException
     * @throws NotFoundException
     */
    public function __invoke(): void
    {
        if( is_array( $this->routes ) && count( $this->routes ) > 0 )
        {
            foreach( $this->routes as $definition )
            {
                $route = $this->create( $definition ) ;
                if( $route instanceof Route )
                {
                    $route() ;
                }
            }
        }
    }

    /**
     * Creates a new Route instance from a definition array or Route object.
     *
     * @param array|Route|null $definition Route definition or existing Route
     * @return Route|null The created Route instance or null if invalid
     */
    public function create( array|Route|null $definition ) :?Route
    {
        if( $definition instanceof Route )
        {
            return $definition ;
        }

        if( is_array( $definition ) && isAssociative( $definition ) )
        {
            $clazz = $definition[ self::CLAZZ ] ?? GetRoute::class ;
            $route = new $clazz( $this->container , clean
            ([
                Route::CONTROLLER_ID => $definition[ Route::CONTROLLER_ID ] ?? $this->controllerID ?? null ,
                Route::METHOD        => $definition[ Route::METHOD        ] ?? null ,
                Route::NAME          => $definition[ Route::NAME          ] ?? null ,
                Route::PROPERTY      => $definition[ Route::PROPERTY      ] ?? null ,
                Route::ROUTE         => $definition[ Route::ROUTE         ] ?? $this->route ,
                Route::SUFFIX        => $definition[ Route::SUFFIX        ] ?? null ,
            ])) ;

            if( $route instanceof Route )
            {
                return $route;
            }
        }

        return null ;
    }

    /**
     * Converts a route path from 'foo/bar' to 'foo.bar'.
     *
     * @param string $route Route path
     * @return string Dotified route path
     */
    public function dotify( string $route ) :string
    {
        if( str_contains( $route , Char::SLASH  ) )
        {
            return str_replace( Char::SLASH  , Char::DOT , $route ) ; // Transform the 'foo/bar' route path in 'foo.bar'
        }
        return $route ;
    }

    /**
     * Executes a callable or an array of callables.
     *
     * @param mixed $routes Callable or array of callables
     * @return void
     */
    public function execute( mixed $routes ) :void
    {
        if( is_callable( $routes ) )
        {
            $routes() ;
        }
        else if( is_array( $routes ) )
        {
            foreach ( $routes as $route )
            {
                if( is_callable( $route ) )
                {
                    $route() ;
                }
            }
        }
    }

    /**
     * Returns the fully qualified route name, including prefix and suffix.
     *
     * @return string Route name
     */
    public function getName() :string
    {
        $name = trim( $this->name ?? $this->dotify( $this->getRoute() )  , Char::DOT ) ;
        return trim( implode( Char::DOT , [ $this->prefix , $name , $this->suffix ] ) , Char::DOT ) ;
    }

    /**
     * Returns the safe main route representation starting with '/'.
     *
     * @return string Route path
     */
    public function getRoute() :string
    {
        return Char::SLASH . ltrim( $this->route ?? Char::EMPTY , Char::SLASH ) ;
    }
}