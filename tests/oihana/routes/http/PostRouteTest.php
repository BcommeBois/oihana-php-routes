<?php

namespace tests\oihana\routes\http;

use DI\Container;
use DI\DependencyException;
use DI\NotFoundException;
use oihana\enums\http\HttpMethod;
use oihana\routes\http\PostRoute;
use PHPUnit\Framework\TestCase;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;
use Slim\App;
use Slim\Factory\AppFactory;
use Slim\Routing\Route;

final class PostRouteTest extends TestCase
{
    public function testInternalMethodIsPost(): void
    {
        $this->assertSame(HttpMethod::post, PostRoute::INTERNAL_METHOD);
    }

    /**
     * @throws ContainerExceptionInterface
     * @throws DependencyException
     * @throws NotFoundException
     * @throws NotFoundExceptionInterface
     */
    public function testInvokeRegistersPostRouteWithControllerPostMethod(): void
    {
        $routePath = '/api/test';

        $container = new Container();
        AppFactory::setContainer($container);
        $app = AppFactory::create();
        $container->set(App::class, $app);

        $controller = new class
        {
            public function post(): string { return 'post-called'; }
        };
        $container->set('my.controller', $controller);

        $route = new PostRoute($container, [
            'controllerID' => 'my.controller',
            'route'        => ltrim($routePath, '/'),
        ]);

        $route();

        $routes = $app->getRouteCollector()->getRoutes();
        $this->assertCount(1, $routes);

        $registered = array_shift($routes);
        $this->assertInstanceOf(Route::class, $registered);
        $this->assertSame($routePath, $registered->getPattern());
        $this->assertSame(['POST'], $registered->getMethods());
        $this->assertSame([$controller, 'post'], $registered->getCallable());
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
            public function create(): string { return 'create-called'; }
        };
        $container->set('my.controller', $controller);

        $route = new PostRoute($container, [
            'controllerID' => 'my.controller',
            'route'        => 'foo',
            'method'       => 'create',
        ]);

        $route();

        $routes = $app->getRouteCollector()->getRoutes();
        $registered = array_shift($routes);
        $this->assertSame([$controller, 'create'], $registered->getCallable());
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

        $route = new PostRoute($container, [
            'controllerID' => 'missing.controller',
            'route'        => 'foo',
        ]);

        $route();

        $this->assertCount(0, $app->getRouteCollector()->getRoutes());
    }
}
