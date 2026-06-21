<?php

namespace tests\oihana\routes\http;

use DI\Container;
use DI\DependencyException;
use DI\NotFoundException;
use oihana\enums\http\HttpMethod;
use oihana\routes\http\PatchRoute;
use PHPUnit\Framework\TestCase;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;
use Slim\App;
use Slim\Factory\AppFactory;
use Slim\Routing\Route;

final class PatchRouteTest extends TestCase
{
    public function testInternalMethodIsPatch(): void
    {
        $this->assertSame(HttpMethod::patch, PatchRoute::INTERNAL_METHOD);
    }

    /**
     * @throws ContainerExceptionInterface
     * @throws DependencyException
     * @throws NotFoundException
     * @throws NotFoundExceptionInterface
     */
    public function testInvokeRegistersPatchRouteWithControllerPatchMethod(): void
    {
        $routePath = '/api/test';

        $container = new Container();
        AppFactory::setContainer($container);
        $app = AppFactory::create();
        $container->set(App::class, $app);

        $controller = new class
        {
            public function patch(): string { return 'patch-called'; }
        };
        $container->set('my.controller', $controller);

        $route = new PatchRoute($container, [
            'controllerID' => 'my.controller',
            'route'        => ltrim($routePath, '/'),
        ]);

        $route();

        $routes = $app->getRouteCollector()->getRoutes();
        $this->assertCount(1, $routes);

        $registered = array_shift($routes);
        $this->assertInstanceOf(Route::class, $registered);
        $this->assertSame($routePath, $registered->getPattern());
        $this->assertSame(['PATCH'], $registered->getMethods());
        $this->assertSame([$controller, 'patch'], $registered->getCallable());
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
            public function update(): string { return 'update-called'; }
        };
        $container->set('my.controller', $controller);

        $route = new PatchRoute($container, [
            'controllerID' => 'my.controller',
            'route'        => 'foo',
            'method'       => 'update',
        ]);

        $route();

        $routes = $app->getRouteCollector()->getRoutes();
        $registered = array_shift($routes);
        $this->assertSame([$controller, 'update'], $registered->getCallable());
    }

    /**
     * @throws ContainerExceptionInterface
     * @throws DependencyException
     * @throws NotFoundException
     * @throws NotFoundExceptionInterface
     */
    public function testInvokeDoesNothingWhenControllerMissing(): void
    {
        $container = new Container();
        AppFactory::setContainer($container);
        $app = AppFactory::create();
        $container->set(App::class, $app);

        $route = new PatchRoute($container, [
            'controllerID' => 'missing.controller',
            'route'        => 'foo',
        ]);

        $route();

        $this->assertCount(0, $app->getRouteCollector()->getRoutes());
    }
}
