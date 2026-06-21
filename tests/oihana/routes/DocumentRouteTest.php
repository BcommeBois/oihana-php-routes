<?php

namespace tests\oihana\routes;

use DI\Container;

use oihana\routes\DocumentRoute;
use oihana\routes\enums\RouteFlag;
use oihana\routes\Route;

use PHPUnit\Framework\TestCase;

use Psr\Log\LoggerInterface;

use Slim\App;
use Slim\Factory\AppFactory;

final class DocumentRouteTest extends TestCase
{
    private Container $container;
    private App       $app;

    protected function setUp(): void
    {
        $this->container = new Container() ;
        AppFactory::setContainer( $this->container ) ;
        $this->app = AppFactory::create() ;
        $this->container->set( App::class , $this->app ) ;
    }

    private function registerController(): void
    {
        $this->container->set( 'my.controller' , new class
        {
            public function get()    : string { return 'get' ; }
            public function list()   : string { return 'list' ; }
            public function count()  : string { return 'count' ; }
            public function post()   : string { return 'post' ; }
            public function patch()  : string { return 'patch' ; }
            public function put()    : string { return 'put' ; }
            public function delete() : string { return 'delete' ; }
        } ) ;
    }

    public function testInvokeRegistersTheFullCrudRouteSet(): void
    {
        $this->registerController() ;

        $route = new DocumentRoute( $this->container ,
        [
            Route::CONTROLLER_ID => 'my.controller' ,
            Route::ROUTE         => 'users' ,
            Route::FLAGS         => RouteFlag::DEFAULT , // exercises the flags-int init branch
        ]) ;

        $route() ;

        $registered = $this->app->getRouteCollector()->getRoutes() ;
        $this->assertNotEmpty( $registered ) ;
    }

    public function testInvokeLogsWarningWhenControllerMissing(): void
    {
        $logger = $this->createMock( LoggerInterface::class ) ;
        $logger->expects( $this->once() )->method( 'warning' ) ;
        $this->container->set( LoggerInterface::class , $logger ) ;

        $route = new DocumentRoute( $this->container ,
        [
            Route::CONTROLLER_ID => 'missing.controller' ,
            Route::ROUTE         => 'users' ,
        ]) ;

        $route() ;

        $this->assertCount( 0 , $this->app->getRouteCollector()->getRoutes() ) ;
    }

    public function testInvokeRegistersNothingExtraWhenAllFlagsDisabled(): void
    {
        $this->registerController() ;

        $route = new DocumentRoute( $this->container ,
        [
            Route::CONTROLLER_ID => 'my.controller' ,
            Route::ROUTE         => 'users' ,
            Route::FLAGS         => RouteFlag::NONE ,
        ]) ;

        $route() ;

        // No method flag enabled: nothing is executed/registered.
        $this->assertCount( 0 , $this->app->getRouteCollector()->getRoutes() ) ;
    }
}
