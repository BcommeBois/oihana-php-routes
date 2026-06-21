<?php

namespace tests\oihana\routes\http;

use DI\Container;
use DI\DependencyException;
use DI\NotFoundException;
use oihana\enums\http\HttpMethod;
use oihana\routes\http\GetRoute;
use oihana\routes\http\SearchRoute;
use PHPUnit\Framework\TestCase;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;
use Slim\App;
use Slim\Factory\AppFactory;
use Slim\Routing\Route;

final class SearchRouteTest extends TestCase
{
    public function testExtendsGetRoute(): void
    {
        $this->assertTrue(is_subclass_of(SearchRoute::class, GetRoute::class));
    }

    public function testInternalMethodIsSearch(): void
    {
        $this->assertSame(HttpMethod::search, SearchRoute::INTERNAL_METHOD);
    }

    /**
     * @throws ContainerExceptionInterface
     * @throws DependencyException
     * @throws NotFoundException
     * @throws NotFoundExceptionInterface
     */
    public function testInvokeRegistersGetRouteCallingControllerSearchMethod(): void
    {
        $routePath = '/api/test';

        $container = new Container();
        AppFactory::setContainer($container);
        $app = AppFactory::create();
        $container->set(App::class, $app);

        $controller = new class
        {
            public function search(): string { return 'search-called'; }
        };
        $container->set('my.controller', $controller);

        $route = new SearchRoute($container, [
            'controllerID' => 'my.controller',
            'route'        => ltrim($routePath, '/'),
        ]);

        $route();

        $routes = $app->getRouteCollector()->getRoutes();
        $this->assertCount(1, $routes);

        $registered = array_shift($routes);
        $this->assertInstanceOf(Route::class, $registered);
        $this->assertSame($routePath, $registered->getPattern());
        $this->assertSame(['GET'], $registered->getMethods());
        $this->assertSame([$controller, 'search'], $registered->getCallable());
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
            public function query(): string { return 'query-called'; }
        };
        $container->set('my.controller', $controller);

        $route = new SearchRoute($container, [
            'controllerID' => 'my.controller',
            'route'        => 'foo',
            'method'       => 'query',
        ]);

        $route();

        $routes = $app->getRouteCollector()->getRoutes();
        $registered = array_shift($routes);
        $this->assertSame([$controller, 'query'], $registered->getCallable());
    }
}
