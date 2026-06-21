<?php

namespace tests\oihana\routes\http;

use DI\Container;
use DI\DependencyException;
use DI\NotFoundException;
use oihana\enums\http\HttpMethod;
use oihana\routes\http\HttpMethodRoute;
use PHPUnit\Framework\TestCase;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;
use Slim\App;
use Slim\Factory\AppFactory;

final class HttpMethodRouteTest extends TestCase
{
    private function newConcrete( Container $container , array $init = [] ): HttpMethodRoute
    {
        return new class( $container , $init ) extends HttpMethodRoute
        {
            public bool $registered = false ;
            public mixed $lastHandler = null ;

            protected function registerRoute( callable $handler ): void
            {
                $this->registered  = true ;
                $this->lastHandler = $handler ;
            }
        };
    }

    /**
     * @throws ContainerExceptionInterface
     * @throws DependencyException
     * @throws NotFoundException
     * @throws NotFoundExceptionInterface
     */
    public function testInternalMethodDefaultsToGet(): void
    {
        $container = new Container();
        AppFactory::setContainer($container);
        $container->set(App::class, AppFactory::create());

        $route = $this->newConcrete($container);

        $this->assertSame(HttpMethod::get, $route->method);
    }

    /**
     * @throws ContainerExceptionInterface
     * @throws DependencyException
     * @throws NotFoundException
     * @throws NotFoundExceptionInterface
     */
    public function testInitializeMethodRespectsInitMethod(): void
    {
        $container = new Container();
        AppFactory::setContainer($container);
        $container->set(App::class, AppFactory::create());

        $route = $this->newConcrete($container, ['method' => 'customMethod']);

        $this->assertSame('customMethod', $route->method);
    }

    /**
     * @throws ContainerExceptionInterface
     * @throws DependencyException
     * @throws NotFoundException
     * @throws NotFoundExceptionInterface
     */
    public function testInvokeDoesNothingWhenControllerNotRegistered(): void
    {
        $container = new Container();
        AppFactory::setContainer($container);
        $container->set(App::class, AppFactory::create());

        $route = $this->newConcrete($container, [
            'controllerID' => 'missing.controller',
            'route'        => 'foo',
        ]);

        $route();

        $this->assertFalse($route->registered, 'registerRoute must not be called when controller is missing');
    }

    /**
     * @throws ContainerExceptionInterface
     * @throws DependencyException
     * @throws NotFoundException
     * @throws NotFoundExceptionInterface
     */
    public function testInvokeDoesNothingWhenMethodMissingOnController(): void
    {
        $container = new Container();
        AppFactory::setContainer($container);
        $container->set(App::class, AppFactory::create());

        $controller = new class { /* no methods */ };
        $container->set('my.controller', $controller);

        $route = $this->newConcrete($container, [
            'controllerID' => 'my.controller',
            'route'        => 'foo',
            'method'       => 'nonExistent',
        ]);

        $route();

        $this->assertFalse($route->registered, 'registerRoute must not be called when method is missing');
    }

    /**
     * @throws ContainerExceptionInterface
     * @throws DependencyException
     * @throws NotFoundException
     * @throws NotFoundExceptionInterface
     */
    public function testInvokeCallsRegisterRouteWithControllerCallable(): void
    {
        $container = new Container();
        AppFactory::setContainer($container);
        $container->set(App::class, AppFactory::create());

        $controller = new class
        {
            public function get(): string { return 'ok'; }
        };
        $container->set('my.controller', $controller);

        $route = $this->newConcrete($container, [
            'controllerID' => 'my.controller',
            'route'        => 'foo',
        ]);

        $route();

        $this->assertTrue($route->registered);
        $this->assertIsCallable($route->lastHandler);
        $this->assertSame([$controller, 'get'], $route->lastHandler);
    }
}
