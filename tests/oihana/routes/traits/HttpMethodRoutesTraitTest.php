<?php

namespace tests\oihana\routes\traits;

use DI\Container;
use PHPUnit\Framework\TestCase;
use oihana\routes\enums\RouteFlag;
use oihana\routes\http\GetRoute;
use oihana\routes\http\OptionsRoute;
use oihana\routes\http\PostRoute;
use oihana\routes\traits\HttpMethodRoutesTrait;
use oihana\routes\Route;
use InvalidArgumentException;
use Slim\App;
use Slim\Factory\AppFactory;

class HttpMethodRoutesTraitTest extends TestCase
{
    private object $mock;

    protected function setUp(): void
    {
        $this->mock = new class {
            use HttpMethodRoutesTrait;

            public Container $container;
          };
    }

    /**
     * Host with a real container + App so method()/options() can build Route subclasses.
     */
    private function routeHost( int $flags = RouteFlag::DEFAULT ): object
    {
        $container = new Container() ;
        AppFactory::setContainer( $container ) ;
        $container->set( App::class , AppFactory::create() ) ;

        $host = new class {
            use HttpMethodRoutesTrait ;
            public Container $container ;
            public ?string $controllerID = 'my.controller' ;
        } ;

        $host->container = $container ;
        $host->initializeFlags( $flags ) ;

        return $host ;
    }

    public function testInitializeMethodsSetsProperties()
    {
        $init = [
            'delete' => 'deleteMethod',
            'get'    => 'getMethod',
            'list'   => 'listMethod',
            'patch'  => 'patchMethod',
            'post'   => 'postMethod',
            'put'    => 'putMethod',
        ];

        $this->mock->initializeMethods($init);

        $this->assertSame('deleteMethod', $this->mock->delete);
        $this->assertSame('getMethod', $this->mock->get);
        $this->assertSame('listMethod', $this->mock->list);
        $this->assertSame('patchMethod', $this->mock->patch);
        $this->assertSame('postMethod', $this->mock->post);
        $this->assertSame('putMethod', $this->mock->put);
    }

    public function testMethodThrowsExceptionForInvalidClass()
    {
        $this->expectException(InvalidArgumentException::class);
        $routes = [];
        $this->mock->method('NonExistentClass', $routes, '/path', 'METHOD');
    }

    public function testEnabledMethodsAppendRouteInstances(): void
    {
        $host   = $this->routeHost( RouteFlag::DEFAULT ) ;
        $routes = [] ;

        $host->count   ( $routes , '/users' ) ;
        $host->delete  ( $routes , '/users' ) ;
        $host->get     ( $routes , '/users' ) ;
        $host->list    ( $routes , '/users' ) ;
        $host->patch   ( $routes , '/users' ) ;
        $host->post    ( $routes , '/users' ) ;
        $host->put     ( $routes , '/users' ) ;
        $host->options ( $routes , '/users' ) ;

        $this->assertCount( 8 , $routes ) ;
        $this->assertContainsOnlyInstancesOf( Route::class , $routes ) ;
        $this->assertInstanceOf( OptionsRoute::class , end( $routes ) ) ;
    }

    public function testDisabledFlagsAppendNothing(): void
    {
        $host   = $this->routeHost( RouteFlag::NONE ) ;
        $routes = [] ;

        $host->count ( $routes , '/users' ) ;
        $host->delete( $routes , '/users' ) ;
        $host->get   ( $routes , '/users' ) ;
        $host->list  ( $routes , '/users' ) ;
        $host->patch ( $routes , '/users' ) ;
        $host->post  ( $routes , '/users' ) ;
        $host->put   ( $routes , '/users' ) ;

        $this->assertSame( [] , $routes ) ;
    }

    public function testOptionsWithFalseFlagAppendsNothing(): void
    {
        $host   = $this->routeHost() ;
        $routes = [] ;

        $host->options( $routes , '/users' , false ) ;

        $this->assertSame( [] , $routes ) ;
    }

    public function testMethodBuildsTheRequestedRouteClass(): void
    {
        $host   = $this->routeHost() ;
        $routes = [] ;

        $host->method( PostRoute::class , $routes , '/users' , 'createUser' ) ;

        $this->assertCount( 1 , $routes ) ;
        $this->assertInstanceOf( PostRoute::class , $routes[0] ) ;
    }

    public function testGetUsesDefaultMethodPropertyWhenNoneGiven(): void
    {
        $host   = $this->routeHost() ;
        $host->get = 'fetchAll' ;
        $routes = [] ;

        $host->get( $routes , '/users' ) ;

        $this->assertInstanceOf( GetRoute::class , $routes[0] ) ;
        $this->assertSame( 'fetchAll' , $routes[0]->method ) ;
    }
}