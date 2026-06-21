<?php

namespace tests\oihana\routes\http;

use DI\Container;
use DI\DependencyException;
use DI\NotFoundException;
use oihana\enums\http\HttpMethod;
use oihana\routes\http\DeleteAllRoute;
use oihana\routes\http\DeleteRoute;
use PHPUnit\Framework\TestCase;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;
use Slim\App;
use Slim\Factory\AppFactory;
use Slim\Routing\Route;

final class DeleteAllRouteTest extends TestCase
{
    public function testExtendsDeleteRoute(): void
    {
        $this->assertTrue(is_subclass_of(DeleteAllRoute::class, DeleteRoute::class));
    }

    public function testInternalMethodIsDeleteAll(): void
    {
        $this->assertSame(HttpMethod::deleteAll, DeleteAllRoute::INTERNAL_METHOD);
    }

    /**
     * @throws ContainerExceptionInterface
     * @throws DependencyException
     * @throws NotFoundException
     * @throws NotFoundExceptionInterface
     */
    public function testInvokeRegistersDeleteRouteCallingControllerDeleteAllMethod(): void
    {
        $routePath = '/api/test';

        $container = new Container();
        AppFactory::setContainer($container);
        $app = AppFactory::create();
        $container->set(App::class, $app);

        $controller = new class
        {
            public function deleteAll(): string { return 'deleteAll-called'; }
        };
        $container->set('my.controller', $controller);

        $route = new DeleteAllRoute($container, [
            'controllerID' => 'my.controller',
            'route'        => ltrim($routePath, '/'),
        ]);

        $route();

        $routes = $app->getRouteCollector()->getRoutes();
        $this->assertCount(1, $routes);

        $registered = array_shift($routes);
        $this->assertInstanceOf(Route::class, $registered);
        $this->assertSame($routePath, $registered->getPattern());
        $this->assertSame(['DELETE'], $registered->getMethods());
        $this->assertSame([$controller, 'deleteAll'], $registered->getCallable());
    }

    /**
     * @throws ContainerExceptionInterface
     * @throws DependencyException
     * @throws NotFoundException
     * @throws NotFoundExceptionInterface
     */
    public function testInvokeRespectsCustomMethodFromInit(): void
    {
        $container = new Container();
        AppFactory::setContainer($container);
        $app = AppFactory::create();
        $container->set(App::class, $app);

        $controller = new class
        {
            public function truncate(): string { return 'truncate-called'; }
        };
        $container->set('my.controller', $controller);

        $route = new DeleteAllRoute($container, [
            'controllerID' => 'my.controller',
            'route'        => 'foo',
            'method'       => 'truncate',
        ]);

        $route();

        $routes = $app->getRouteCollector()->getRoutes();
        $registered = array_shift($routes);
        $this->assertSame([$controller, 'truncate'], $registered->getCallable());
    }
}
