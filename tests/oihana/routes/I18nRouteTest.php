<?php

namespace tests\oihana\routes;

use DI\Container;

use oihana\routes\I18nRoute;
use oihana\routes\Route;

use PHPUnit\Framework\TestCase;

use Psr\Log\LoggerInterface;

use Slim\App;
use Slim\Factory\AppFactory;

final class I18nRouteTest extends TestCase
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

    public function testInvokeRegistersLocalizedPropertyRoutes(): void
    {
        $this->container->set( 'my.controller' , new class
        {
            public function get()   : string { return 'get' ; }
            public function patch() : string { return 'patch' ; }
        } ) ;

        $route = new I18nRoute( $this->container ,
        [
            Route::CONTROLLER_ID => 'my.controller' ,
            Route::ROUTE         => 'articles' ,
            Route::PROPERTY      => 'title' ,
        ]) ;

        $route() ;

        $this->assertNotEmpty( $this->app->getRouteCollector()->getRoutes() ) ;
    }

    public function testInvokeLogsWarningWhenControllerMissing(): void
    {
        $logger = $this->createMock( LoggerInterface::class ) ;
        $logger->expects( $this->once() )->method( 'warning' ) ;
        $this->container->set( LoggerInterface::class , $logger ) ;

        $route = new I18nRoute( $this->container ,
        [
            Route::CONTROLLER_ID => 'missing.controller' ,
            Route::ROUTE         => 'articles' ,
            Route::PROPERTY      => 'title' ,
        ]) ;

        $route() ;

        $this->assertCount( 0 , $this->app->getRouteCollector()->getRoutes() ) ;
    }
}
